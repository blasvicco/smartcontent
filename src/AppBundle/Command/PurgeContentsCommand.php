<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class PurgeContentsCommand extends ContainerAwareCommand {

	private function purgeContent() {
		$doctrine = $this->getContainer()->get('doctrine');
		$em = $doctrine->getManager();
		$stmt = $em->getConnection()->prepare(
			'DELETE FROM smart_content WHERE status IN ("error", "discarded") '.
			'AND DATEDIFF(NOW(), created) >= 7'
		);
		return $stmt->execute();
	}

	protected function configure() {
		$this->setName('PurgeContents')->setDescription('Purgeing error and discarded contents');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->purgeContent();
		$output->writeln('Executed');
	}
}
?>