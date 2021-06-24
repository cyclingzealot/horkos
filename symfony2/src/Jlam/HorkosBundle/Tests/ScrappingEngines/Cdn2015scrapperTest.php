<?php

namespace Spotlight\RestBundle\Tests;

use  Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Jlam\HorkosBundle\Cdn2015scrapper;

class Cdn2015scrapperTest extends WebTestCase
{

	
	
	public function setUp() {
		parent::setUp();
		
		$client = static::createClient(array(
    		'environment' => 'test',
		));
				
		Cdn2015scrapper::initialize($client->getContainer());
	}

	/**
	 * Dummy test to see if file properly boots.
	 * And to avoid phpunit giving us warning
	 * for file not having a test
	 */
	public function testDummy() {
		$this->assertTrue(TRUE);
	}
	
	
	
	public function testGetRidingIdentifiers() {
		$results = Cdn2015scrapper::getRidingIdentifiers();
		
		$this->assertTrue(is_array($results));
		
		$this->assertGreaterThan(0, count($results));
		
		foreach($results as $identifier) {
			$this->assertTrue(is_numeric($identifier));
		}
	}

}