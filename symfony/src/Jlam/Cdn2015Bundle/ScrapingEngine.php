<?php

namespace Jlam\Cdn2015Bundle;

use Jlam\Cdn2015Bundle\Entity\Riding;
use Jlam\Cdn2015Bundle\Scrapper;

/**
 *
 * Abstract class of a srapping engine, containing
 * functions common to scrappers for  all elections
 * regardless of data presentation.
 * 
 * Hopefully the class extending this one 
 * is the only code that needs 
 * to change from one election to the next.
 * (other then esthetic changes between elections)
 * 
 *
 * @author jlam
 *        
 */
abstract class ScrapingEngine implements Scrapper {
	
	protected static $language;
	
	protected static $pathFormat;
	
	protected static $identifier;
	
	protected static $source;
	
	protected static $initialized;
	
	protected static $container;
	
	/*
	 * @var array
	 */
	protected static $byRidingResults;
	
	
	/**
	 * All scrapping engines should be static,
	 * hence why the constructor is protected and 
	 * not public 
	 */
	protected function __construct() {}
	
	
	public static function initialize($container, $language = 'en') {
		if (self::$initialized)  return;
		
		self::setLanguage($language);
		
		self::setContainer($container);
		
		self::$initialized = TRUE;
	}
	
	/**
	 * Return array of riding objects
	 * 
	 * array($ridingIdentifier) =>
	 * 		arrayObjects
	 * 
	 * The twig template get then get its required results with
	 * a for each loop and:
	 * 		riding.name
	 * 		riding.source
	 * 		riding.unrepresentedVotes
	 * 
	 * See http://stackoverflow.com/questions/14413550/twig-access-object#answer-14413657
	 */
	
	
	public static function getByRidingResults() {
		return self::$byRidingResults;
	}
	
	
	
	/**
	 * This is probably wrong architecturally.
	 * 
	 * However, the scrapping engine isn't a service.
	 * It shouldn't be visible from anywhere.
	 * 
	 * All I want is the scrapping engine to have access 
	 * to the same services as a controller does.
	 * 
	 * 
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
	public static function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
		self::$container = $container;
	}
	
	
	public static function addRiding(Riding $riding) {
		$riding->updateTallies();
		
		self::$byRidingResults[$riding->getIdentifier()] = $riding;
	}
	
	protected static function setLanguage($language = 'en') {
		self::$language = $language;
	}
	
	
	protected static function getLanguage() {
		return self::$language;
	}
	
	protected static function setSource($source) {
		self::$source = $source;
	}
	
	/**
	 * 
	 * @param string $message
	 * @return  LoggerInterface 
	 */
	protected static function getLogger($message = null) {
		$logger = self::$container->get('logger');
		
		if($message)  $logger->info($message);
		
		return $logger;
	}
	
	
	
	protected static function addLog($message) {
		$logger = self::getLogger();
		
		$logger->info($message);
	}
	
	protected static function addError($message) {
		$logger = self::getLogger();
		
		$logger->error($message);
		
	}
	
}

?>