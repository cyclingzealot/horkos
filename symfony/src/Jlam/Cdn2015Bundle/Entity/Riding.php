<?php

namespace Jlam\Cdn2015Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
