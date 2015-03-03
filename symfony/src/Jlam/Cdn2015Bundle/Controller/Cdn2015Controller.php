<?php

namespace Jlam\Cdn2015Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Jlam\Cdn2015Bundle\Cdn2015scrapper;
use Jlam\Cdn2015Bundle\Entity\Riding;
use Jlam\Cdn2015Bundle\TallyHolder;
use Jlam\Cdn2015Bundle\Twig\SafeDivideExtension;

class Cdn2015Controller extends Controller
{
    public function indexAction()
    {
		#If a cache is available, use it.
	
    	

    	# Set the logger for the riding entity
    	Riding::setLogger($this->get('logger'));
    	
		#If not, slurp data
		Cdn2015scrapper::initialize($this->container);
		Cdn2015scrapper::scrape();
		
		
		
		### Prepare the rendering #####################################################
		$partyTally			= Riding::getPartyTally()->getTally();
		$jurisdictionTally	= Riding::getJurisdictionTally()->getTally();
		$partyTallyWasted	= $partyTally['wasted'];
		arsort($partyTallyWasted);
		
		$summary = array(
				'jurisdictionName'	=> 'Canada',
			'electionName'			=> 'Canadian 2015',
				'source'			=> \Jlam\Cdn2015Bundle\Cdn2015scrapper::getSource(),
				'totalWastedVotes'	=> array_sum($partyTallyWasted),
				'tweetHandle'		=> '#elxn42',
				'gitHubSource'		=> 'https://github.com/cyclingzealot/cdn2015',
		);
	
	    #Render
        $response = $this->render('JlamCdn2015Bundle:Cdn2015:index.html.twig', array(
        	'ridings'	 		=> Riding::getAllRdings(),
        	'partyTallyWasted' 	=> $partyTallyWasted,
        	'jurisdiction'		=> $jurisdictionTally,
        	'summary'			=> $summary,
        ));
        
        #Save into caching
        
        
		#Return the controller 
        return $response;
    }
}
