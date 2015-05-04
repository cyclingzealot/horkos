<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;


class Ab2015scrapper extends ScrapingEngine {

	
	public static function scrape() {
		$logger = self::getLogger('Starting scrape...');
		
		$ridingIdentfiers = self::getRidingIdentifiers();

		self::addLog('Got ' . count($ridingIdentfiers) . ' ridings: ' . join(', ', $ridingIdentfiers));
		
		foreach ($ridingIdentfiers as $i ) {
			$url = self::getFinalPath($i);

			self::addLog("Getting results for riding $i $url...");
			
			$riding = new Riding();
			$riding->setSource($url);
			
			$html = @file_get_contents ( $url );
			
			if($html === FALSE) {
				self::addLog("Warning: no content for riding $i at $url");
				continue;
			}
			
			/*
			$doc = new \DOMDocument ();
			self::setErrorHandler();
			$doc->loadHTML ( $html );
			self::setErrorHandler(TRUE);
			$xpath = new \DOMXPath ( $doc );
			*/

			$string     = self::grep($html, 'Unofficial Poll Results', TRUE);
			$ridingName = self::grep($string, 'Unofficial Poll Results - [0-9][0-9]* ([A-Z-]*)')[0];
			
			self::addLog("Got ridingName: $ridingName");
			$riding->setName($ridingName);
			
			// nodes = $xpath->query('/html/body/div[2]/div[2]/div[2]/div[3]/table/tbody');
			
			// cho "For " . $ridingNames[$i] . "\n";
			// ar_export($tables->item(0)->textContent);
			// cho "\n\n";
			
			
			$string  = self::grep($html, 'ColHeadingCA', TRUE);
			preg_match_all("|<SPAN STYLE='font-size: 12pt'>([A-Z]*)</SPAN>|", $string[0], $matches);
			self::addLog('Got ' . count($matches[1]) . ' matches: ' . join(',', $matches[1]));
			
			$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([0-9,]*)<BR>|", $stringVotes[2], $matchesVotes);
			self::addLog('Got ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));

			foreach($matches[1] as $index => $party) {
				$votes = str_replace(',', '', $matchesVotes[1][$index]);
				$riding->setVotes($party, $votes);
			}

			# Find and set eligeable voters
			$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([0-9,]*)|", $stringVotes[1], $matchesVotes);
			self::addLog('Got for voters count ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));
			$numVoters = str_replace(',', '', $matchesVotes[1][0]);
			self::addLog("Number of voters: $numVoters");
			$riding->setEligibleVoters($numVoters);
			
			# Find and save all votes (aka total votes)
			$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([0-9,]*)</TABLE>|", $stringVotes[5], $matchesVotes);
			self::addLog('Got ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));
			$totalVotes = str_replace(',', '', $matchesVotes[1][0]);
			self::addLog("Number of total votes: $totalVotes");
			$riding->setAllRidingVotes($totalVotes);
			
			# Update talies 
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
		
		$html = @file_get_contents ( $url );

		if($html === FALSE) {
			self::setError("Content returned by riding identifiers page $url was empty");
			return array();
		}
		
		
		$strings = explode("\n", $html);
		self::addLog('Got ' . count($strings) . ' strings');
		$identifiers = self::grep($strings, '([0-9]+)BE\.htm');
		self::addLog('Got ' . count($strings) . ' strings');

		/*
		foreach($strings as $string) {
			$href = self::cut($string, '"', 1);

			self::addLog("string is $string, href is $href");

			$match = preg_match_all('/([0-9]+)/', $href, $matches);

			$identifiers[] = $matches[1];
		}
		*/

		$identifiers = array_unique($identifiers);

		return $identifiers;

	}
	
	
	protected static function getLanguageEquivalent() {
		$lang = self::getLanguage();
		
		if(empty($lang))  $lang = 'en';
		
		return $lang[0];
	}
	
	
	public static function getFinalPath($identifier) {
		$logger = self::getLogger('In final path');
		

		$lang = self::getLanguageEquivalent();
		
		/*
		if(in_array($identifier, self::$devRidings)) {
				$logger->warn("Dev files not implemented yet for identifier $identifer");
		}
		*/
	
		return "http://results.elections.ab.ca/${identifier}BE.htm";
		
		
	}
	
	
	public static function getSummary() {
		return array(
				'jurisdictionName'	=> 'Alberta',
				'electionName'		=> 'Alberta 2015',
				'source'			=> self::getSource(),
				'tweetHandle'		=> '#abvote',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/horkos',
		);
	}
}

?>
