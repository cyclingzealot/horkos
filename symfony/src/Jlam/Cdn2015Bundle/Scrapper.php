<?php

namespace Jlam\Cdn2015Bundle;

interface Scrapper {
	


	/**
	 * Starts sarapping
	 *
	 * The meat of the scraping engine.
	 *
	 * When one riding is done, it should be added using
	 * self::addRiding($riding);
	 *
	 *
	 */
	static function scrape();
	
}

?>