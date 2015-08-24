<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;
use Jlam\HorkosBundle\Scrapper;

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
	
	protected static $error;
	
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
		
		self::$error = FALSE;
	}
	
	
	protected static function setError($message = null) {
		self::$error = TRUE;
		
		if(is_string($message)) {
			self::addError($message);
		}
	}
	
	public static function getScraperError() {
		return self::$error;
	}
	
	
	public static function validate() {
		if(count(Riding::getAllRidings()) == 0) {
			self::setError("getAllRidings returned 0 ridings");
		}
	}
	
	public static function grep($strings, $pattern, $returnLines = FALSE) {
		$matches = array ();
		
		if(is_string($strings))
			$strings = explode("\n", $strings);
		
		foreach ($strings as $str) {
			#self::addLog("Searching $pattern in $str");
			if (!$returnLines && preg_match ("/$pattern/", $str, $m)) {
				$match = $m[1];
				#self::addLog("Matched $match");
				$matches[] = $match;
			} elseif (strpos($str, $pattern) !== FALSE) {
				#self::addLog("Adding $str cause it contains $pattern");
			    $matches[] = $str;
			} else {
				#self::addLog("$str does not contain $pattern");
			}
		}
	
		self::addLog('Returning ' . count($matches) . ' matches: ' . join('#*#', $matches));

		return $matches;
	}
	
	
	public static function cut($string, $delimiter, $field) {
		self::addLog("Cutting $string with $delimiter, field $field");
		$array = explode($delimiter, $string);
		
		$return = $array[$field];
		self::addLog("Returning $return");
		return $return;
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
	
	protected static function getRidingPaths($jurisdiction, $lang) {
		$kernelRootDir = self::$container->getParameter('kernel.root_dir');
		
		$dataDir = $kernelRootDir . "/../../eshu/data/$jurisdiction/$lang/ready/";
		
		self::addLog("Opening data dir $dataDir");
		
		$listOfFiles	= scandir($dataDir);
		$fileCount 		= count($listOfFiles);
		
		if($listOfFiles === FALSE) {
			self::addError("SERIOUS: scandir of $dataDir returned FALSE");
		}
		
		self::addLog("Found $fileCount files");
		
		$returnArray = array();
		
		foreach($listOfFiles as $fileName) {
			if(in_array($fileName, array('.', '..'))) {
				continue;
			}
			
			$ridingNumber = explode('.', $fileName)[0];
			
			$path = "$dataDir/$fileName";
			
			self::addLog("Adding $path for $ridingNumber");
			
			$returnArray[intval($ridingNumber)] = $path;
		}
		
		return $returnArray;
	}
	
	public static function getSource() {return self::$source;}
	
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
	
	/**
	 * Sets the error hangler to monolog 
	 * to suppress the DOMDocument::loadHTML() warnings
	 * 
	 * Most of this function copied from 
	 * @author Jeremy Cook http://jeremycook.ca/2012/10/02/turbocharging-your-logs/
	 */
	protected static function setErrorHandler($reset = FALSE) {
		if($reset === TRUE) {
			restore_error_handler();
		}
		elseif($reset === FALSE) {
			set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext){
				$message = 'Error of level ';
				switch ($errno) {
					case E_USER_ERROR:
						$message .= 'E_USER_ERROR';
						break;
					case E_USER_WARNING:
						$message .= 'E_USER_WARNING';
						break;
					case E_USER_NOTICE:
						$message .= 'E_USER_NOTICE';
						break;
					case E_STRICT:
						$message .= 'E_STRICT';
						break;
					case E_RECOVERABLE_ERROR:
						$message .= 'E_RECOVERABLE_ERROR';
						break;
					case E_DEPRECATED:
						$message .= 'E_DEPRECATED';
						break;
					case E_USER_DEPRECATED:
						$message .= 'E_USER_DEPRECATED';
						break;
					case E_NOTICE:
						$message .= 'E_NOTICE';
						break;
					case E_WARNING:
						$message .= 'E_WARNING';
						break;
					default:
						$message .= sprintf('Unknown error level, code of %d passed', $errno);
				}
				$message .= sprintf(
						'. Error message was "%s" in file %s at line %d.',
						$errstr,
						$errfile,
						$errline
				);
				
				$logger = self::getLogger();
				
				
				$logger->warn($message);
				
				return true;//Returning false will mean that PHP's error handling mechanism will not be bypassed.
			});
		}
	}
	
}

?>
