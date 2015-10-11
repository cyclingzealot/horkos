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
	const DEFAULT_ELECTION		= 'cdn2015';
	const CACHE_TTL_SECS		= 30;

    public function indexAction()
    {

    	#Get request parameters
    	$election			= $this->getRequest()->get('election');
    	$language			= $this->getRequest()->get('language', 'en');
    	$subJurisdiction	= $this->getRequest()->get('subJur');
    	$fresh				= $this->getRequest()->get('fresh');

		#Setup caching
    	$cacheKey			= "$election.$language";
    	if(!empty($subJurisdiction)) {
    		$cacheKey .= ".$subJurisdiction";
    	}

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
		$magnitude = 1;
		$subJurs	= null;

		if(! $engineClassName::getScraperError()) {
			$partyTally			= Riding::getPartyTally(FALSE)->getTally();
			$jurisdictionTally	= Riding::getJurisdictionTally()->getTally();
			#$partyTallyWasted	= $partyTally['wasted'];
			#arsort($partyTallyWasted);
			$summary['totalWastedVotes']	= $jurisdictionTally['wasted'];
			$magnitude			= Riding::calculateMagnitudeWinner();
			$subJurs			= $engineClassName::getSubJurisdictions();
		}
		else
		{ # If there is an error
			$partyTally			= array();
			$jurisdictionTally	= array();
			$partyTallyWasted	= array();
			$summary['totalWastedVotes']	= null;
		}

	    #Render
	   	$renderArray = array(
        	'ridings'			=> Riding::getAllRdingsSorted(),
        	'partyTally' 		=> $partyTally,
        	'jurisdiction'		=> $jurisdictionTally,
        	'summary'			=> $summary,
        	'election'			=> $election ? $election : self::DEFAULT_ELECTION,
        	'error'				=> $engineClassName::getScraperError(),
        	'magnitude' 		=> $magnitude,
        	'currentURI'		=> $this->getRequest()->getUri(),
        	'currentSubJur'		=> $subJurisdiction,
        );

	   	if(! empty($subJurs)) {
	   		$renderArray['subJurisdictions']  = $subJurs;
	   	}

        $response = $this->render('JlamHorkosBundle:Horkos:index.html.twig', $renderArray);




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
