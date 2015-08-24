<?php

namespace Jlam\HorkosBundle\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Jlam\HorkosBundle\Entity\Riding;
use Jlam\HorkosBundle\TallyHolder;
use Jlam\HorkosBundle\Twig\SafeDivideExtension;
use Jlam\HorkosBundle\phpFastCache;

class HorkosController extends Controller
{
	
	const BASE_DIR_SCRAPPERS	= 'Jlam\HorkosBundle\\';
	const DEFAULT_ELECTION		= 'ab2015';
	const CACHE_TTL_SECS		= 30;
	
    public function indexAction()
    {
    	
    	#Get request parameters
    	$election			= $this->getRequest()->get('election');
    	$language			= 'en';
    	$fresh				= $this->getRequest()->get('fresh');
    	
		#Setup caching
    	$cacheKey			= "$election.$language";
    	$root = $this->get('kernel')->getRootDir();
    	require_once("$root/../src/Jlam/HorkosBundle/phpfastcache-final/phpfastcache.php");
    	phpFastCache::setup("storage", "auto");
    	$cache = new phpFastCache();
    	
    	
    	#If a cache is available, use it and return it right away.
    	$response = $cache->get($cacheKey);
    	
    	if($response !== null && $fresh !== 'yes') {
    		return $response;
    	}
    	/* Doing it with ACL:
    	if ($responseObject = $this->get('cache')->fetch($cacheKey)) {
    		$response = unserialize($responseObject);
    		return $response;
    	}
    	*/
    	
    	
    	
    	# Get the engine class name
    	$engineClassName	= self::getScrappingEngineClassName($election);

    	# Set the logger for the riding entity
    	Riding::setLogger($this->get('logger'));
    	
		#If not, slurp data
		$engineClassName::initialize($this->container);
		$engineClassName::scrape();
		$engineClassName::validate();

		
		
		### Prepare the rendering #####################################################
		$summary						= $engineClassName::getSummary();
		
		if(! $engineClassName::getScraperError()) {
			$partyTally			= Riding::getPartyTally()->getTally();
			$jurisdictionTally	= Riding::getJurisdictionTally()->getTally();
			$partyTallyWasted	= $partyTally['wasted'];
			arsort($partyTallyWasted);
			$summary['totalWastedVotes']	= array_sum($partyTallyWasted);
		} else {
			$partyTally			= array();
			$jurisdictionTally	= array();
			$partyTallyWasted	= array();
			$summary['totalWastedVotes']	= null;
		}
	
	    #Render
        $response = $this->render('JlamHorkosBundle:Horkos:index.html.twig', array(
        	'ridings'	=> Riding::getAllRdingsSorted(),
        	'partyTally' 	=> $partyTally,
        	'jurisdiction'	=> $jurisdictionTally,
        	'summary'	=> $summary,
        	'election'	=> $election ? $election : self::DEFAULT_ELECTION,
        	'error'		=> $engineClassName::getScraperError(),
        ));
        
        
        #Save into caching
        $date = new \DateTime();
        $date->modify('+'. self::CACHE_TTL_SECS .' seconds');
        
        $response->setPublic();
        $response->setExpires($date);
        $response->setMaxAge(self::CACHE_TTL_SECS);
        $response->setSharedMaxAge(self::CACHE_TTL_SECS);
        
		$cache->set($cacheKey, $response, self::CACHE_TTL_SECS);
                
        
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
