<?php

namespace Jlam\HorkosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Jlam\HorkosBundle\Entity\Riding;
use Jlam\HorkosBundle\TallyHolder;
use Jlam\HorkosBundle\Twig\SafeDivideExtension;

class HorkosController extends Controller
{
	
	const BASE_DIR_SCRAPPERS	= 'Jlam\HorkosBundle\\';
	const DEFAULT_ELECTION		= 'cdn2015';
	
    public function indexAction()
    {
		#If a cache is available, use it.
	
    	
    	
    	
    	# Get the engine class name
    	$election			= $this->getRequest()->get('election');
    	$engineClassName	= self::getScrappingEngineClassName($election);

    	# Set the logger for the riding entity
    	Riding::setLogger($this->get('logger'));
    	
		#If not, slurp data
		$engineClassName::initialize($this->container);
		$engineClassName::scrape();
		
		
		### Prepare the rendering #####################################################
		$partyTally			= Riding::getPartyTally()->getTally();
		$jurisdictionTally	= Riding::getJurisdictionTally()->getTally();
		$partyTallyWasted	= $partyTally['wasted'];
		arsort($partyTallyWasted);
		
		$summary						= $engineClassName::getSummary();
		$summary['totalWastedVotes']	= array_sum($partyTallyWasted);
	
	    #Render
        $response = $this->render('JlamHorkosBundle:Horkos:index.html.twig', array(
        	'ridings'	 		=> Riding::getAllRdings(),
        	'partyTallyWasted' 	=> $partyTallyWasted,
        	'jurisdiction'		=> $jurisdictionTally,
        	'summary'			=> $summary,
        ));
        
        #Save into caching
        
        
		#Return the controller 
        return $response;
    }
    
    
    private static function getScrappingEngineClassName($electionShorthand = null) {
    	$engineClassNames = array(
    		'cdn2015'	=> 'Cdn2015scrapper',
    		'ab2015'	=> 'Ab2015scrapper',
    	);
    	
    	if(!isset($engineClassNames[$electionShorthand])) 
    		$electionShorthand = self::DEFAULT_ELECTION;
    	
    	$engineClassName =  $engineClassNames[$electionShorthand];
    	
    	return self::BASE_DIR_SCRAPPERS . $engineClassName;
    }
}
