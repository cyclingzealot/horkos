<?php

namespace src\Jlam\Cdn2015Bundle;

/**
 *
 * @author jlam
 *        
 */
abstract class ScrapingEngine {
	
	protected static $language;
	
	protected static $pathFormat;
	
	protected static $identifier;
	
	protected static $source;
	
	const MODE_GROUPED  = 'grouped';
	const MODE_PER_PAGE	= 'perPage';
	
	/**
	 */
	function __construct($pathFormat, $language) {
		
	}
	
	/**
	 * The meat of the scraping engine.
	 * This is the only code that should change 
	 * for each given election
	 * 
	 * This should call $this->setIdentifier if necessary
	 * 
	 * @param string $id
	 */
	public abstract static function scrape($id = null);
	
	/**
	 * Determines the final path of the function given the:
	 * - Path format (self::pathFormat)
	 * - Language (self::language)
	 * - Identifer given to scrapper
	 * 
	 */
	protected static function calcFinalPath() {
		
	}
	
	/**
	 * Determines if the scrapping mode is:
	 * - Grouped: all ridings on one page
	 * - PerPage:   One riding per page
	 */
	private static function calcSrapeMode() {
		
	}
	
	protected static function isModePerPage() {
		
	}
	
	
	protected static function setLanguage($language) {
		self::$language = $language;
	}
	
	
	protected static function setSource($source) {
		self::$source = $source;
	}
	
	
}

?>