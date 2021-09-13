<?php

namespace App\HorkosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\HorkosBundle\TallyHolder;

#Tentaively removing this as Symfony5 / PHP 7 is not allowing us to use special class name String
#use Symfony\Component\Translation\Tests\String;

/**
 * Riding
 *
 * @ORM\Table()
 * @ORM\Entity
 *
 * @author jlam@credil.org
 */
class Riding
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=500)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="candidates", type="array")
     */
    private $candidates;

    /**
     * @var integer
     *
     * @ORM\Column(name="eligibleVoters", type="bigint")
     */
    private $eligibleVoters;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=5000)
     */
    private $source;



    private $localRaceTally;


    private $allRidingVotes;



    private static $logger;






    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=50)
     *
     */
    private $identfier;



    /**
     * Array in the structure of array(
     * 		'wasted' => array(
     * 			$nameForParty1	=> $wastedVotesForParty1,
     * 			$nameForParty2	=> $wastedVotesForParty2,
     * 		)
     * 		'valid'	 => array(
     * 			$nameForParty1	=> $validVotesForParty1,
     * 			$nameForParty2	=> $validVotesForParty2,
     * 		)
     * 		//Leading means where the votes for that party would count if
     * 		//counting finished
     * 		'leading'=> array(
     * 			$nameForParty1	=> $votesForParty1whereParty1Leading
     * 			$nameForParty2	=> $votesForParty2whereParty2leading
     * )
     *
     * @var array
     * @author jlam@credil.org
     */
    static protected $partyTally;
    static protected $jurisdictionTally;



    static protected $talliesInitialized;

    static protected $ridingsContainer;


    public function __construct() {
    	self::initializeTallies();

    	$this->localRaceTally = new TallyHolder();

    	self::$ridingsContainer[] = $this;

    }


    /**
     * Adds a candidate, also takes care of
     * adding the party of that candidate
     */
    public function addCandidate() {

    }





    public function getParticipationRate() {
    	return $this->calcParticipationRate();
    }

    public function getUnrepresentedVotes() {
    	return $this->calcUnrepresentedVotes();
    }


    public function setVotes($party, $votes) {
        $votes = str_replace(',', '', $votes);
        $party = trim($party);
    	$this->localRaceTally->add(array($party=>$votes));
    }


    public function getValidVotes() {
    	return $this->calcValidVotes();
    }



    /**
     * Calculates the participation rate
     *
     * Returns a float between 0 and 1
     *
     * @return float
     */
    public function calcParticipationRate() {
    	$voters = $this->getEligibleVoters();

    	$totalVotes = $this->allRidingVotes;

    	if($voters == 0) {
    		self::$logger->warn("0 eligible voters for " . $this->name);
    		return 0;
    	}

    	return $totalVotes / $voters;
    }


    /**
     * Calculates the number of unrepresented votes
     * in absolute numbers
     *
     * @return integer
     */
    public function calcUnrepresentedVotes() {
    	$localTally		= $this->localRaceTally->getTally();
    	$wastedTally	= self::copyWithoutHighest($localTally);

    	return array_sum($wastedTally);
    }


    public function calcValidVotes() {
    	$tally = $this->localRaceTally->getTally();

    	return array_sum($tally);
    }


    public function getLocalResults() {
		return $this->localRaceTally;
    }


    /**
     * Calculates the number of unrepresented votes
     * for each party.  Independants are agregated together
     *
     * @return array
     *
     */
    public function calcUnrepresentedVotesByParty() {

    }

    /**
     * calls on the updating of the tallies
     *
     * @return null
     */
    public function updateTallies() {
    	$localTally		= $this->localRaceTally->getTally();
    	$wastedTally	= self::copyWithoutHighest($localTally);
    	$winnerCount	= self::leadingOnly($localTally); // count winning seats
        self::addLog("localTally for $this->name is " . var_export($localTally, TRUE));
	$effectiveVotes = self::keepOnlyHighest($localTally); // with how many votes

    	self::$jurisdictionTally->add(array(
    		'eligible'=>$this->getEligibleVoters(),
    		'valid'		=>array_sum($localTally),
    		'wasted'	=>array_sum($wastedTally),
    		'all'		=>$this->allRidingVotes
    	));

    	self::$partyTally->add(array(
    			'wasted' 	=>$wastedTally,
    			'valid'  	=>$localTally,
    			'leading'	=>$winnerCount,
    			'effective'	=>$effectiveVotes,
    	));

        self::addLog("wastedTally is " . var_export($wastedTally, TRUE));
        self::addLog("jurisdictionTally is " . var_export(self::$jurisdictionTally, TRUE));
        self::addLog("partyTally is " . var_export(self::$partyTally, TRUE));

    	/*
    	 * Riding ($this) is already added to the
    	 * allRidingsContainer in the
    	 * contructor, no need to add here
    	 */
    }



    /**
     * Given the party tally, calculates the order of magitude of
     * seats for the largest winner for X seats per F votes
     *
     * Anoter suggested formula is
     * F = 50 * Total valid votes / total number of seats
     *
     */
    public static function calculateMagnitudeWinner() {
    	$perPartyTally = self::getPartyTally(TRUE);

    	$perPartyTally = $perPartyTally->getTally();

    	$totalVotesLeading = current(self::findMaxSet($perPartyTally['valid']));

    	$orderMagnitude = intval(floor(log10(max($totalVotesLeading, 1))));

    	return pow(10, $orderMagnitude);
    }


    public static function copyWithoutHighest($arrayIn) {
    	$array = $arrayIn;

    	$keyMax = self::findKeyOfMaxValue($arrayIn);

    	unset($array[$keyMax]);

    	return $array;
    }

    public static function keepOnlyHighest($arrayIn) {
    	$array = $arrayIn;


        ### If all values are 0,
        ### just return the array as 8it was given
        if (self::isAllZero($arrayIn)) {
            return $arrayIn;
        }

    	$keyMax = self::findKeyOfMaxValue($arrayIn);

    	return array($keyMax=>$array[$keyMax]);
    }



    public static function leadingOnly($arrayIn) {
    	$array = $arrayIn;

        ### If all values are 0, there is no one leading
        if (self::isAllZero($arrayIn)) {
            return array();
        }

    	$keyMax = self::findKeyOfMaxValue($arrayIn);

    	return array($keyMax=>1);

    }



    public static function findKeyOfMaxValue($arrayIn) {

    	return key(self::findMaxSet($arrayIn));
    }

    public static function isAllZero($arrayIn) {
        return (count(array_unique($arrayIn)) === 1 && end($arrayIn) === 0);
    }


    public static function findMaxSet($arrayIn) {
        $keyMax = null;
        $max = null;

        ### If all values are 0, there is no maximum to return
        if (self::isAllZero($arrayIn)) {
            return array();
        }

    	foreach($arrayIn as $key=>$value) {
    		if(!isset($max) || $value>$max) {
    			$max	= $value;
    			$keyMax	= $key;
    		}
    	}

    	return array($keyMax => $max);
    }


    /**
     * Called in the constructor so you don't
     * have to do it in the Controller
     *
     */
    public static function initializeTallies() {
    	if(self::$talliesInitialized)  return;

    	self::$partyTally			=	new TallyHolder();
    	self::$jurisdictionTally	=	new TallyHolder();

    	self::$talliesInitialized = TRUE;
    }


    public static function getPartyTally($byStatistic=TRUE) {
	if($byStatistic) {
    		return self::$partyTally;
	}

	$invertedTally = new TallyHolder();

	$currentTally = self::$partyTally;

	foreach($currentTally->getTally() as $stat => $partyData) {

		foreach($partyData as $party => $value) {
			$invertedTally->add(array($party=>array($stat=>$value)));
		}
	}

	return $invertedTally;
    }


    public static function getJurisdictionTally() {
    	return self::$jurisdictionTally;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Riding
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set candidates
     *
     * @param array $candidates
     * @return Riding
     */
    public function setCandidates($candidates)
    {
        $this->candidates = $candidates;

        return $this;
    }


    /**
     * Set identifier
     *
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
    	$this->identfier = $identifer;
    }


    public function getIdentifier() {
    	return $this->identfier;
    }

    /**
     * Get candidates
     *
     * @return array
     */
    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * Set eligibleVoters
     *
     * @param integer $eligibleVoters
     * @return Riding
     */
    public function setEligibleVoters($eligibleVoters)
    {
        $this->eligibleVoters = $eligibleVoters;

        return $this;
    }

    /**
     * Get eligibleVoters
     *
     * @return integer
     */
    public function getEligibleVoters()
    {
        return $this->eligibleVoters;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Riding
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }


    public static function getAllRidings() {
    	$allRidings = self::$ridingsContainer;
    	return $allRidings;
    }

    public static function getAllRdingsSorted() {
    	$allRidings = self::getAllRidings();

		usort($allRidings, array('App\HorkosBundle\Entity\Riding', 'sortRidings'));
		$allRidings = array_reverse($allRidings);

    	return $allRidings;
    }

	public static function sortRidings($a, $b) {
		$diff = $a->getUnrepresentedVotes() - $b->getUnrepresentedVotes();
		file_put_contents('/tmp/horkosDiff.txt', "$diff, ", FILE_APPEND);
		return $diff;
	}


    public function setAllRidingVotes($allRidingVotes) {
    	$this->allRidingVotes = $allRidingVotes;
    }

	public function getAllRidingVotes() {
		return $this->allRidingVotes;
    }

    public static function setLogger($logger) {
    	self::$logger = $logger;
    }


    public static function addLog($message) {
    	self::$logger->info($message);
    }

}
