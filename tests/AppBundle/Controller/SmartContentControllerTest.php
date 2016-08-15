<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Entity\User;

class SmartContentControllerTest extends WebTestCase {
	const GAID = '182b15b8eebb201c:a902431f346fd244:com:en:US';
	private function setUpData($client) {
		$doctrine = $client->getContainer()->get('doctrine');
		$em = $doctrine->getManager();
		$stmt = $em->getConnection()->prepare(
			'INSERT INTO google_alert
			(google_alert_id, user_id, keyword, often, lang, country) VALUES
			("'.self::GAID.'", 2, "zarlanga", "asItHappens", "en", "US")'
		);
		$stmt->execute();
		for ($i = 1; $i < 16; $i++) {
			$stmt = $em->getConnection()->prepare(
				'INSERT INTO smart_content
				(google_alert_id, user_id, content, created, url, status) VALUES
				("'.self::GAID.'", 2, "Content zarlanga '.$i.'", "'.date('Y-m-d H:i:s').'", "fakeurl", "queued")'
			);
			$stmt->execute();
		}
	}
	
	private function cleanTestData($client) {
		$doctrine = $client->getContainer()->get('doctrine');
		$em = $doctrine->getManager();
		$stmt = $em->getConnection()->prepare(
			'DELETE FROM google_alert WHERE google_alert_id = "'.self::GAID.'"'
		);
		$stmt->execute();
		$stmt = $em->getConnection()->prepare(
			'DELETE FROM smart_content WHERE google_alert_id = "'.self::GAID.'"'
		);
		$stmt->execute();
	}
	
	private function doLogin($client) {
		$crawler = $client->request('GET', '/login');
		$form = $crawler->selectButton('_submit')->form([
			'_username'  => User::USERNAME,
			'_password'  => User::PASSWORD,
		]);
		$client->submit($form);
		$this->assertTrue($client->getResponse()->isRedirect());
		$crawler = $client->followRedirect();
		return $client;
	}
	
	public function testIndex() {
		$client = static::createClient();
		$client = $this->doLogin($client);
		$this->setUpData($client);
		$crawler = $client->request('GET', '/smartcontent/'.self::GAID);
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$paginator = $crawler->filter('.pagination');
		$secondPage = $paginator->filter('a')->eq(2)->link()->getUri();
		$crawler = $client->request('GET', $secondPage);
		$content = $crawler->filter('tbody > tr > td')->eq(3)->text();
		$this->assertContains('Content zarlanga 5...', $content);
	}
	
	public function testEdit() {
		$client = static::createClient();
		$client = $this->doLogin($client);
		$crawler = $client->request('GET', '/smartcontent/'.self::GAID);
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$edit = $crawler->filter('td > a')->eq(0)->link()->getUri();
		$crawler = $client->request('GET', $edit);
		$form = $crawler->selectButton('submit')->form();
		$token = $form->get('smart_content[_token]')->getValue();
		$toPost = [
			'smart_content[status]' => 'parsed',
			'smart_content[content]' => '<p>Content zarlanga 15</p>',
			'smart_content[_token]' => $token
		];
		$form = $crawler->selectButton('submit')->form($toPost);
		$client->submit($form);
		$this->assertTrue($client->getResponse()->isRedirect());
		$crawler = $client->followRedirect();
		$crawler = $client->request('GET', '/smartcontent/'.self::GAID);
		$parsed = $crawler->filter('td')->eq(4)->text();
		$this->assertContains('parsed', $parsed);
		$this->cleanTestData($client);
	}
}
