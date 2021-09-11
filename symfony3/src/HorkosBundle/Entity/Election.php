<?php

namespace App\HorkosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Election
 * 
 * Mostly used for static methods containing the logic for
 * calculating wasted votes
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Election
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    
    
}
