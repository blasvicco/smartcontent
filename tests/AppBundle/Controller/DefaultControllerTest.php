<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase {

	public function testIndex() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertContains('Welcome to SmartContent', $crawler->filter('#welcome h1')->text());
	}
	
	public function testRestricted() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/restricted');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$this->assertContains('What are you trying to do dude???!!!', $crawler->filter('#noWelcome h1')->text());
	}
}
