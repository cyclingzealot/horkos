<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;


class Ab2015scrapper extends ScrapingEngine {
	
	
	public static function scrape() {
		$logger = self::getLogger('Starting scrape...');
		
		$ridingIdentfiers = self::getRidingIdentifiers();
		
		foreach ($ridingIdentfiers as $i ) {
			self::addLog("Getting results for riding $i $url...");
			
			$riding = new Riding();
			$riding->setSource($url);
			
			$html = file_get_contents ( $url );
			
			
			
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
			
			$rows = $tables->item ( 0 )->getElementsByTagName ( 'tr' );
			
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
		
		$url = "http://results.elections.ab.ca/wtResultsBE.htm";	
		
		self::setSource($url);
		
		$html = file_get_contents ( $url );
			
		$doc = new \DOMDocument ();
		libxml_use_internal_errors(true);
		$doc->loadHTML ( $html );
		libxml_use_internal_errors(false);
		$xpath = new \DOMXPath ( $doc );
			
		$xPathQuery = 'body > center:nth-child(6) > b:nth-child(2) > table:nth-child(3) > tbody:nth-child(1) > tr:nth-child(3) > td:nth-child(1) > a:nth-child(1)';
		$xPathQuery = 'body / table';
		
		self::addLog("xPath: $xPathQuery");
		$ridingList = $xpath->query ( $xPathQuery );
		
		// RENDU ICI
		
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
	
	
	public static function getSummary() {
		return array(
				'jurisdictionName'	=> 'Alberta',
				'electionName'		=> 'Alberta 2015',
				'source'			=> self::getSource(),
				'tweetHandle'		=> '#elxn42',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/horkos',
		);
	}
}

?>