<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\SmartContent;

class ProcessContentsCommand extends ContainerAwareCommand {

	private function getQueuedSmartContent() {
		$doctrine = $this->getContainer()->get('doctrine');
		$em = $doctrine->getManager();
		$query = $em->createQuery('SELECT sc, ga.keyword FROM AppBundle:SmartContent sc
			INNER JOIN AppBundle:GoogleAlert ga WITH (sc.googleAlertId = ga.googleAlertId)
			WHERE sc.status like :status
			ORDER BY sc.created ASC')->setParameter('status', 'queued');
		return $query->getResult();
	}

	private function retrieveContent($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if ($httpCode >= 200 && $httpCode < 300 && !$errno) {return $result;}
		print 'Curl error: ' . $errno . ' ' . $error . "\n";
		return false;
	}

	function isConsistent($content, $keyword) {
		$SummarizerPro = new \SummarizerPro();
		$SummarizerPro->scoreWords($content);
		$matches = [];
		preg_match_all('/<(h6|h5|h4|h3|h2|h1)(\s\S.+)?>/', $content, $matches);
		$h = count($matches[0]);
		$matches = [];
		preg_match_all('/<(p)(\s\S.+)?>/', $content, $matches);
		$p = count($matches[0]);
		return ($p > $h) && $SummarizerPro->isAnImportantWord($keyword);
	}

	function parseContent($content, $keyword) {
		$matches = [];
		$content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
		preg_match_all('/<(p|h6|h5|h4|h3|h2|h1)(\s\S.+)?>.+<\/(p|h6|h5|h4|h3|h2|h1)>/', $content, $matches);
		$content = '';
		if ($matches[0]) {
			for ($i = count($matches[0]) - 1; $i >= 0; $i --) {
				$element = $matches[0][$i];
				if (empty($content) && (strpos($element, '<p') === false)) continue;
				$element = preg_replace('/<(\w+)[^>]*>/', '<$1>', $element);
				$content = $element . (strpos($element, '<p') === false ? "\n\n" : "\n") . $content;
			}
		}
		$content = strip_tags($content, '<h1><h2><h3><h4><h5><h6><p>');
		$SummarizerPro = new \SummarizerPro();
		$SummarizerPro->scoreWords($content);
		if ($SummarizerPro->isAnImportantWord($keyword)) {
			$SummarizerPro->removeFromIgnore($keyword);
			$content = $SummarizerPro->summarize($content);
		}
		return $content;
	}

	protected function configure() {
		$this->setName('ProcessContents')->setDescription('Processing the queued content to retrieve the information');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$queuedSmartContent = $this->getQueuedSmartContent();
		$em = $this->getContainer()->get('doctrine')->getManager();
		foreach ($queuedSmartContent as $smartContent) {
			$keyword = $smartContent['keyword'];
			$smartContent = $smartContent[0];
			$content = $this->retrieveContent($smartContent->getUrl());
			if (!empty($content)) {
				$content = $this->parseContent($content, $keyword);
				$smartContent->setStatus($this->isConsistent($content, $keyword) ? 'parsed' : 'discarded');
				$smartContent->setContent($content);
			} else {
				$smartContent->setStatus('error');
			}
			$em->persist($smartContent);
			$em->flush();
		}
		$output->writeln('Executed');
	}
}
?>