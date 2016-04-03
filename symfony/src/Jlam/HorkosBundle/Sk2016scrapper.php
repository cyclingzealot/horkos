<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;


class Sk2016scrapper extends ScrapingEngine {

    const JURISDICTION_SHORTHAND = 'sk';

	public static function scrape() {
		$logger = self::getLogger('Starting offline scrape...');

		#$ridingIdentfiers = self::getRidingIdentifiers();

        $langPathPart = self::langPathPartLookup('en');

        $ridingPaths = parent::getRidingPaths(self::JURISDICTION_SHORTHAND, $langPathPart);

        $ridingCount = count($ridingPaths);

        self::addLog("$ridingCount file found");

        if($ridingCount > 1)  {
            self::addLog("WARNING: more than one file found in \$ridingPaths!");
        }

        $dataSourcePath = $ridingPaths[0];

        $csvFile = fopen($dataSourcePath, 'r');

        $line=0;
        while($row = fgetcsv($csvFile) !== FALSE) {
            $line++;
            self::addLog("At line $line of CSV file");
            #Skil until line 12 of csv file
            if($line < 12) {
                continue;
            }

            $riding = new Riding();
            $riding->setSource(self::getSource());

            $riding->setName($row[0]);

            $partyColumnArray = array(
                2 => 'GP',
                3 => 'IND',
                4 => 'LIB',
                5 => 'NDP',
                6 => 'PC',
                7 => 'SP',
                8 => 'WIP',
            );

            foreach ($partyColumnArray as $index => $party){
                $riding->setVotes($party, $row[$index]);
            }

            $riding->setEligibleVoters(round(1033381/60));

            $riding->updateTallies();
        }



	}


    public static function initialize($container, $language = 'en') {
		$url = "http://results.elections.sk.ca/Home/LiveResults";

		self::setSource($url);

        parent::initialize($container);

    }


    static function langPathPartLookup($lang = 'en') {
        $langReturn = 'e';

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
     * PRETTY SURE THIS CAN BE DELETED FOR SK, AS WE USE ONE CSV FILE
	 */
	public static function getRidingIdentifiers() {
		#Path to riding list: //*[@id="message_board"]/ul

		$ridingIdentifiers = array();

		$lang = self::getLanguageEquivalent();

		$url = "http://results.elections.sk.ca/Home/LiveResults";

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
				'jurisdictionName'	=> 'Saskatchewan',
				'electionName'		=> 'Saskatchewan 2016',
				'source'			=> self::getSource(),
				'tweetHandle'		=> '#skvotes',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/horkos',
		);
	}
}

?>
