<?php

namespace Spotlight\RestBundle\Tests;

use  Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Jlam\HorkosBundle\TallyHolder;


class TallyHolderTest extends KernelTestCase {
	
	
	public function testDummy() {
		$this->assertTrue(TRUE);
	}
	
	
	public function testAdd() {
		$t = new TallyHolder ();
		
		$baseArray = array (
				'France' => 5,
				'Julien' => 10,
				'Maurice' => 20,
				'Daniel' => 17,
				'Canada' => array (
						'Quebec' => 8,
						'Ontario' => 15 
				) 
		);
		
		$additive = array (
			'France' => 2,
				'Julien' => 5,
				'Canada' => array('Quebec'=>3)
		);
		
		$t->add($baseArray);
		
		$t->add($additive);
		
		$expected = array (
				'France' => 7,
				'Julien' => 15,
				'Maurice' => 20,
				'Daniel' => 17,
				'Canada' => array (
						'Quebec' => 11,
						'Ontario' => 15 
				) 
		);
		
		
		$this->assertEquals($expected, $t->getTally());
		
	}
	
}
