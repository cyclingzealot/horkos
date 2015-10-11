<?php

namespace Jlam\HorkosBundle;

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



	static function getScraperError();


	/**
	 * Returns an array with summary information
	 * about an election
	 *
	 * Example of a recommended array for 2015 federal election
	 * array(
				'jurisdictionName'	=> 'Canada',
				'electionName'		=> 'Canadian 2015',
				'source'			=> self::getSource(),
				'totalWastedVotes'	=> array_sum($partyTallyWasted),
				'tweetHandle'		=> '#elxn42',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/cdn2015',
		);
	 *
	 */
	static function getSummary();

	static function initialize($container, $language = 'en');


	static function getJurisdictionShorthand();
	static function setJurisdictionShorthand($subJur);


	static function getSubJurisdictions();

}

?>