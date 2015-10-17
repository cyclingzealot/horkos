<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;


class Cdn2015scrapper extends ScrapingEngine {

	protected static $devRidings = array(0);

	protected static $source = 'http://enr.elections.ca/ElectoralDistricts.aspx';

	const JURISDICTION_SHORTHAND = 'cdn';


	public static function scrape($lang = 'en') {
		$logger = self::getLogger('Starting offline scrape...');

		$langPathPart = self::langPathPartLookup($lang);

		$ridingPaths = parent::getRidingPaths(self::JURISDICTION_SHORTHAND, $langPathPart);

		$ridingCount = count($ridingPaths);

		self::addLog("$ridingCount ridings found");

		foreach ($ridingPaths as $i => $path ) {
			$sourceUrl = self::generateSource($lang, $i);

			self::addLog("Getting results for riding $i $path...");

			$riding = new Riding();
			$riding->setSource($sourceUrl);

			$html = file_get_contents ( $path );



			$doc = new \DOMDocument ();
			self::setErrorHandler();
			$doc->loadHTML ( $html );
			self::setErrorHandler(TRUE);
			$xpath = new \DOMXPath ( $doc );

			$xPathQuery = '//*[@id="grdResultsucElectoralDistrictResult' . $i . '"]/caption';


			self::addLog("xPath: $xPathQuery");

			$ridingNode = $xpath->query ( $xPathQuery );
			$ridingName = trim ( substr ( $ridingNode->item ( 0 )->textContent, 50 ) );

			self::addLog($ridingName);
			$riding->setName($ridingName);

			$tables = $doc->getElementsByTagName('table');

			// nodes = $xpath->query('/html/body/div[2]/div[2]/div[2]/div[3]/table/tbody');

			// cho "For " . $ridingNames[$i] . "\n";
			// ar_export($tables->item(0)->textContent);
			// cho "\n\n";

			$tablesLength = $tables->length;

			self::addLog("Found $tablesLength items in \$tables");

			$rows = $tables->item ( 1 )->getElementsByTagName ( 'tr' );

			$numRows = $rows->length;

			self::addLog("There are $numRows rows\n");

			$j = 0;

			for($j=0; $j<$numRows-3; $j++) {
				if($j==0)  continue;

				$row = $rows->item($j);

				$cells = $row->getElementsByTagName('td');

				$party = $cells->item(0)->textContent;

				$votes = preg_replace("/[^0-9]/", "", $cells->item(2)->textContent);
				$votes = str_replace(",", "", $votes);

				self::addLog("Scrapped: $party\t$votes\n");
				$riding->setVotes($party, $votes);
			}

			$xPathQuery = '//*[@id="divElectorNumberucElectoralDistrictResult' . $i . '"]/p';
			$numVoters = trim(substr($xpath->query($xPathQuery)->item(0)->textContent, 80));
			$numVoters = str_replace(',', '', $numVoters);
			self::addLog("Number of voters: $numVoters");
			$riding->setEligibleVoters($numVoters);


			$row = $rows->item($numRows-1);
			$cells = $row->getElementsByTagName('td');
			$totalVotes = $cells->item(2)->textContent;
			$totalVotes = str_replace(',', '', $totalVotes);
			self::addLog("Number of total votes: $totalVotes");
			$riding->setAllRidingVotes($totalVotes);

			$riding->updateTallies();

		}



	}



	static function langPathPartLookup($lang = 'en') {
		$langReturn = 'e';

		switch ($lang) {
			case 'fr':
				$langReturn = 'f';
				break;

			case 'en':
			default:
				$langReturn = 'e';
		}

		return $langReturn;
	}


	/**
	 * Since Elections Canada is changing the ridings
	 * listed at http://enr.elections.ca/ElectoralDistricts.aspx,
	 * we add some sample files we have saved as those files dissappear
	 *
	 * @param array $ridingIdentifiers
	 * @return array
	 */
	protected static function addDevRidings($ridingIdentifiers) {
		return array_merge($ridingIdentifiers, self::$devRidings);
	}


	/**
	 * Gets the list of riding identifiers
	 */
	public static function getRidingIdentifiers() {
		#Path to riding list: //*[@id="message_board"]/ul

		$ridingIdentifiers = array();

		$lang = self::getLanguageEquivalent();

		$url = "http://enr.elections.ca/ElectoralDistricts.aspx?lang=$lang";

		self::setSource($url);

		$html = file_get_contents ( $url );

		$doc = new \DOMDocument ();
		libxml_use_internal_errors(true);
		$doc->loadHTML ( $html );
		libxml_use_internal_errors(false);
		$xpath = new \DOMXPath ( $doc );

		$xPathQuery = '//*[@id="message_board"]/ul/li';

		self::addLog("xPath: $xPathQuery");
		$ridingList = $xpath->query ( $xPathQuery );

		$count = $ridingList->length;

		foreach($ridingList as $ridingHTML) {

			$string = $ridingHTML->tagName;

			$aHref = $ridingHTML->getElementsByTagName('a');

			$url = $aHref->item(0)->getAttribute('href');

			$parts = parse_url($url);
			parse_str($parts['query'], $query);
			$ridingIdentifiers[] = $query['ed'];

		}

		return $ridingIdentifiers;
	}


	protected static function getLanguageEquivalent() {
		$lang = self::getLanguage();

		if(empty($lang))  $lang = 'en';

		return $lang[0];
	}


	public static function getFinalPath($identifier) {
		$logger = self::getLogger('In final path');


		$lang = self::getLanguageEquivalent();

		if(in_array($identifier, self::$devRidings)) {
				$logger->warn("Dev files not implemented yet for identifier $identifer");
		}

		return "http://enr.elections.ca/ElectoralDistricts.aspx?ed=$identifier&lang=$lang";


	}


	public static function generateSource($lang, $identifier) {
		return "http://enr.elections.ca/ElectoralDistricts.aspx?ed=$identifier&lang=$lang";
	}


	public static function getSummary() {
		return array(
				'jurisdictionName'	=> 'Canada',
				'electionName'		=> 'Canadian 2015',
				'source'			=> 'http://enr.elections.ca/ElectoralDistricts.aspx',
				'tweetHandle'		=> '#elxn42',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/cdn2015',
		);
	}
}

?>