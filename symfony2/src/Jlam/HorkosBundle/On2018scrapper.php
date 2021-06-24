<?php

namespace Jlam\HorkosBundle;

use Jlam\HorkosBundle\Entity\Riding;


class On2018scrapper extends ScrapingEngine {

    const JURISDICTION_SHORTHAND = 'on';

    const ELECTION_DATE = "2018-06-07";

    const ROOT_SOURCE_URL = "https://www4.elections.on.ca/RealTimeResults";



    public static function getElectionDate() {
        $dateReturn = '1970-01-01';

        if (defined(self::ELECTION_DATE)) {
            $dateReturn = self::ELECTION_DATE;
        }

        return date_create($dateReturn);
    }



	public static function scrape() {
		$logger = self::getLogger('Starting offline scrape...');

		#$ridingIdentfiers = self::getRidingIdentifiers();

        $langPathPart = self::langPathPartLookup('en');

        $ridingPaths = parent::getRidingPaths(self::JURISDICTION_SHORTHAND, $langPathPart);

        $ridingCount = count($ridingPaths);

        self::addLog("$ridingCount file found");

        if($ridingCount != 124)  {
            self::addLog("WARNING:  Not 124 ridings found. $ridingCount found in $ridingPaths!");
        }

        $ridingNameData = json_decode(file_get_contents("https://www4.elections.on.ca/RealTimeResults/api/refdata/geteds/504/en"), TRUE);
        $ridingIdToName = [];
        foreach($ridingNameData as $ridingId => $ridingData) {
            $ridingIdToName[$ridingData["i"]] = $ridingData["n"];
        }


        $partyString = <<<EOT
                [{"n":"INDEPENDENT"},{"n":""},{"n":""},{"n":"PC Party of Ontario"},{"n":"Green Party of Ontario"},{"n":"Ontario NDP/NPD"},{"n":"Ontario Liberal Party"},{"n":"Freedom Party of Ontario"},{"n":"Communist"},{"n":"Ontario Provincial Confederation of Regions Party"},{"n":"Libertarian"},{"n":"Party for People with Special Needs"},{"n":""},{"n":"Paupers"},{"n":"Go Vegan"},{"n":"The People"},{"n":"N O P"},{"n":"CCP"},{"n":"Ontario Moderate Party"},{"n":"Trillium Party TPO"},{"n":"None of the Above Direct Democracy Party"},{"n":"Stop the New Sex-Ed Agenda"},{"n":"CAP"},{"n":"Alliance"},{"n":"The New People's Choice Party of Ontario"},{"n":"Multicultural Party of Ontario"},{"n":"Consensus Ontario"},{"n":"CEP"},{"n":"Stop Climate Change"},{"n":"SRP"},{"n":"Ontario Party"},{"n":"P.O.T."}]
EOT;
        $partyList = json_decode($partyString);
        foreach($partyList as $partyId => $partyData) {
            $partyName = $partyData->n;
            $partyColumnArray[$partyId] = $partyName;
        }

        foreach($ridingPaths as $i=>$dataSourcePath) {
            // get the riding id from the file name, without the suffix
            $id = basename($dataSourcePath, '.json');

            // Initialize riding name
	        $ridingName = $ridingIdToName[$id];
	        self::addLog("Doing $ridingName....");


            //Get riding data stored offline by eshu
	        $json = json_decode(file_get_contents($dataSourcePath), true);

            //SKip if riding data is empty
            if (count($json["cs"]) == 0) {
	            self::addLog("Skipping $ridingName because of no data ....");
                continue;
            }

            //Create object
	        $riding = new Riding();

            //Parse through each candidte result
	        foreach($json["cs"] as $j => $candidatesData) {
	            $riding->setSource(self::getSource());
                $riding->setName($ridingName);
	            $party = $partyColumnArray[$candidatesData["pi"]];
	            $votes = $candidatesData["v"];

                if (strlen($party) == 0) {
                    continue;
                }
                $riding->setVotes($party, $votes);

	            self::addLog("Recorded $votes votes for $party in $ridingName");

	        }

            //I think this is called only when done with the riding
	        $riding->updateTallies();
        }

	}


    public static function initialize($container, $language = 'en') {
		self::setSource(self::ROOT_SOURCE_URL);

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


		/* This seems unused now
	public static function getFinalPath($identifier) {
		$logger = self::getLogger('In final path');


		$lang = self::getLanguageEquivalent();

		if(in_array($identifier, self::$devRidings)) {
				$logger->warn("Dev files not implemented yet for identifier $identifer");
		}

		return "http://results.elections.ab.ca/${identifier}.htm";


	}
		*/


	public static function getSummary() {
		return array(
				'jurisdictionName'	=> 'Ontario',
				'electionName'		=> 'Ontario 2018',
				'source'			=> self::getSource(),
				'tweetHandle'		=> '#OnElxn',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/horkos',
		);
	}
}

?>
