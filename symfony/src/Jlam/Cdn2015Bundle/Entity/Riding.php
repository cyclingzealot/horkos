<?php

namespace Jlam\Cdn2015Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Translation\Tests\String;

/**
 * Riding
 *
 * @ORM\Table()
 * @ORM\Entity
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

    
    
    /**
     * @var string
     * 
     * @ORM\Column(name="identifier", type="string", length=50)
     * 
     */
    private $identfier;
    
    
    
    
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
    	
    }
    
    
     
    
    
    public static function setPartyTallyHolder($partyTally) {
    	
    }
    
    
    public static function setJurisdictionTallyHolder($jurisdictionTally) {
    	
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
}
