<?php

namespace Jlam\Cdn2015Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Translation\Tests\String;
use Jlam\Cdn2015Bundle\TallyHolder;

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
    
    
    
    
    
    
    /**
     * @var string
     * 
     * @ORM\Column(name="identifier", type="string", length=50)
     * 
     */
    private $identfier;
    
    
    static protected $partyWastedVotesTally;
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
    	$this->localRaceTally->add(array($party=>$votes));
    }
    
    
    
    /**
     * Calculates the participation rate
     * 
     * Returns a float between 0 and 1
     * 
     * @return float
     */
    public function calcParticipationRate() {
    	
    }
    
    
    /**
     * Calculates the number of unrepresented votes
     * in absolute numbers
     * 
     * @return integer
     */
    public function calcUnrepresentedVotes() {
    	
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
    	
    	self::$jurisdictionTally->add(array(
    		'elibegable'=>$this->getEligibleVoters(),
    		'valid'		=>array_sum($localTally),
    		'wasted'	=>array_sum($wastedTally),
    		'all'		=>$this->allRidingVotes
    	));
    	
    	self::$partyWastedVotesTally->add($wastedTally);
    }
    
    
    public static function copyWithoutHighest($arrayIn) {
    	$array = $arrayIn;
    	
    	$keyMax = null;
    	$max = null;
    	
    	foreach($array as $key=>$value) {
    		if(!isset($max) || $value>$max) {
				$max	= $value;
				$keyMax	= $key;
    		}
    	}
    	
    	unset($array[$keyMax]);
    	
    	return $array;
    }
    
    
    /**
     * Called in the constructor so you don't 
     * have to do it in the Controller
     * 
     */
    public static function initializeTallies() {
    	if(self::$talliesInitialized)  return;
    	
    	self::$partyTallyHolder =			new TallyHolder();
    	self::$jurisdictionTallyHolder =	new TallyHolder();
    	
    	self::$talliesInitialized = TRUE;
    }
    
    
    public static function getPartyTallyHolder() {
    	return self::$partyTallyHolder;
    }
    
    
    public static function getJurisdictionTallyHolder() {
    	return self::$jurisdictionTallyHolder;
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
    
    
    public static function getAllRdiings() {
    	return self::$ridingsContainer;
    }
    
    
    public function setAllRidingVotes($allRidingVotes) {
    	$this->allRidingVotes = $allRidingVotes;
    }
    
}
