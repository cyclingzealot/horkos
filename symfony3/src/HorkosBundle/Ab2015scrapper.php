<?php

namespace App\HorkosBundle;

use App\HorkosBundle\Entity\Riding;


class Ab2015scrapper extends ScrapingEngine {

    const JURISDICTION_SHORTHAND = 'ab';

    const ELECTION_DATE = "2019-04-16";

   public static function initialize($container, $language = 'en', $electionDate = self::ELECTION_DATE) {
        $url = "https://results.elections.ab.ca/";

        self::setSource($url);

        parent::initialize($container, $language, $electionDate);

    }


	public static function scrape() {
		$logger = self::getLogger('Starting scrape...');

        $logger = self::getLogger('Starting offline scrape...');

        #$ridingIdentfiers = self::getRidingIdentifiers();

        $langPathPart = self::langPathPartLookup('en');

        $ridingPaths = parent::getRidingPaths(self::JURISDICTION_SHORTHAND, $langPathPart);

		#$ridingIdentfiers = self::getRidingIdentifiers();
		#self::addLog('Got ' . count($ridingIdentfiers) . ' ridings: ' . join(', ', $ridingIdentfiers));

		foreach ($ridingPaths as $i=>$dataFilePath ) {
			$url = self::getFinalPath($i);

			self::addLog("Getting results for riding $i in $dataFilePath ($url)...");

			$riding = new Riding();
			$riding->setSource($url);
			$ridingCount = count(Riding::getAllRidings());
			self::addLog("Riding count is $ridingCount");

			$html = file_get_contents ( $dataFilePath );

			if($html === FALSE) {
				self::addLog("Warning: no content for riding $i at $dataFilePath");
				continue;
			}

			$doc = new \DOMDocument ();
			self::setErrorHandler();
			$doc->loadHTML ( $html );
			self::setErrorHandler(TRUE);
			$xpath = new \DOMXPath ( $doc );

            $ridingName = $xpath->query('//h1')->item(0)->textContent;

			#$string     = self::grep($html, '<H1>', TRUE);
			#$ridingName = self::grep($string, '[0-9][0-9]* - ([\. A-Z-]*)');
            #$ridingName = $ridingName[0];
			#$ridingName = substr_replace($ridingName, '', -2);

			self::addLog("Got ridingName: $ridingName");
			$riding->setName($ridingName);

			// nodes = $xpath->query('/html/body/div[2]/div[2]/div[2]/div[3]/table/tbody');

			// cho "For " . $ridingNames[$i] . "\n";
			// ar_export($tables->item(0)->textContent);
			// cho "\n\n";


			#$string  = self::grep($html, 'CHeadCA', TRUE);
			#preg_match_all("|<DIV CLASS=CHPA>([A-Z]*)</DIV>|", $string[0], $matches);
            $tableHeaders = $xpath->query('/html/body/div[2]/table[1]/thead/tr/th');

            $columnIndex = 0;
            self::addLog($tableHeaders->length . ' headers obtained');
            foreach($tableHeaders as $nodeElement) {
                $elements = $nodeElement->childNodes;
                self::addLog($elements->length . ' elements obtained');
                $party = trim($elements->item(2)->textContent);
                #$text = $nodeElement->textContent;
                #$party = (explode('>', $text)[1]);

                self::addLog("Found header for $party for $ridingName");

                $votes = $xpath->query('/html/body/div[2]/table[1]/tbody/tr/td')->item($columnIndex)->textContent;
                $votes = str_replace(',', '', $votes);

                if (ctype_space($votes) || $votes == '') $votes = 0 ;

                self::addLog("Got $votes votes for $party in riding $ridingName");

                $riding->setVotes($party, $votes);
                $columnIndex++;
            }

			#self::addLog('Got for party: ' . count($matches[1]) . ' matches: ' . join(',', $matches[1]));

            /*
			$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([ 0-9,]*)<BR>|", $stringVotes[2], $matchesVotes);
			self::addLog('Got for votes: ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));

			if(count($matches == 0))  self::addError("No matches found for party");

			foreach($matches[1] as $index => $party) {
				$votes = str_replace(',', '', $matchesVotes[1][$index]);

				if(empty($votes))  self::addError("No votes found for $party in $ridingName");

				self::addLog("Setting $votes votes for $party in $ridingName");
				$riding->setVotes($party, $votes);
			}
            */

			# Find and set eligeable voters
			#$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			#preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([0-9,]*)|", $stringVotes[1], $matchesVotes);
			#self::addLog('Got for voters count ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));
			$numVoters = $xpath->query('/html/body/table/tbody[1]/tr/td[2]')->item(0)->textContent;
			self::addLog("Number of voters: $numVoters");
			$riding->setEligibleVoters($numVoters);

			# Find and save all votes (aka total votes)
			#$stringVotes  = self::grep($html, 'ColFooter', TRUE);
			#preg_match_all("|<TD Class=ColFooter ALIGN=RIGHT VALIGN=TOP>([ 0-9,]*)</TABLE>|", $stringVotes[5], $matchesVotes);
			#self::addLog('Got ' . count($matchesVotes[1]) . ' matches: ' . join(' ', $matchesVotes[1]));
			#$totalVotes = str_replace(',', '', $matchesVotes[1][0]);

            $totalVotes = $xpath->query('/html/body/table/tbody[1]/tr/td[9]')->item(0)->textContent;
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

		$url = "http://results.elections.ab.ca/wtResultsPGE.htm";

		self::setSource($url);

		$html = @file_get_contents ( $url );

		if($html === FALSE) {
			self::setError("Content returned by riding identifiers page $url was empty");
			return array();
		}


		$strings = explode("\n", $html);
		self::addLog('Got ' . count($strings) . ' strings');
		$identifiers = self::grep($strings, '([0-9]+)\.htm');
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

    static function langPathPartLookup($lang = 'en') {
        $langReturn = 'en';

        return $langReturn;
    }



	public static function getFinalPath($identifier) {
		$logger = self::getLogger('In final path');


		$lang = self::getLanguageEquivalent();

		/*
		if(in_array($identifier, self::$devRidings)) {
				$logger->warn("Dev files not implemented yet for identifier $identifer");
		}
		*/

		return "http://results.elections.ab.ca/${identifier}.htm";


	}


	public static function getSummary() {
		return array(
				'jurisdictionName'	=> 'Alberta',
				'electionName'		=> 'Alberta 2019',
				'source'			=> self::getSource(),
				'tweetHandle'		=> '#abvote',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/horkos',
		);
	}
}

?>
