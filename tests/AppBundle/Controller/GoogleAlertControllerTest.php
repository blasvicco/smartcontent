<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Entity\User;

class GoogleAlertControllerTest extends WebTestCase {
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
	
	public function testCreatingGoogleAlert() {
		$client = static::createClient();
		$client = $this->doLogin($client);
		$crawler = $client->request('GET', '/googlealert/new');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$form = $crawler->selectButton('submit')->form();
		$token = $form->get('google_alert[_token]')->getValue();
		$toPost = [
			'google_alert[keyword]' => 'TEST',
			'google_alert[often]' => 'onceADay',
			'google_alert[lang]' => 'en',
			'google_alert[country]' => 'US',
			'google_alert[_token]' => $token
		];
		$form = $crawler->selectButton('submit')->form($toPost);
		$client->submit($form);
		$this->assertTrue($client->getResponse()->isRedirect());
		$crawler = $client->followRedirect();
		$crawler = $client->request('GET', '/googlealert');
		$node = $crawler->filter('tbody > tr > td')->eq(1);
		$this->assertContains('TEST', $node->text());
	}
	
	public function testDeleteGoogleAlert() {
		$client = static::createClient();
		$client = $this->doLogin($client);
		$crawler = $client->request('GET', '/googlealert');
		$node = $crawler->filter('tbody > tr > td')->eq(1);
		$this->assertContains('TEST', $node->text());
		$link = $crawler->filter('li > a')->eq(1)->link()->getUri();
		$crawler = $client->request('GET', $link);
		$node = $crawler->filter('tbody > tr > td')->eq(1);
		$this->assertFalse($node->count() > 0);
	}
}
