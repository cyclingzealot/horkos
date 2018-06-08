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
	const DEFAULT_ELECTION		= 'none';
	const CACHE_TTL_SECS		= 1;

    public function indexAction()
    {
        $logger = $this->get('logger');

        $logger->info("Starting...");

    	#Get request parameters
    	$election			= $this->getRequest()->get('election')	?: self::DEFAULT_ELECTION;
    	$language			= 'en';
    	$fresh				= $this->getRequest()->get('fresh');
    	$format				= $this->getRequest()->get('format')	?: 'html';


        if (empty($election) || $election == 'none') {
	        $response = $this->render("JlamHorkosBundle:Horkos:none.html.twig", array(
                        electionsList => self::getElectionNames()
                        ));
            return $response;
        }



		#Setup caching
    	$cacheKey			= "$election.$language.$format";
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

		if(! $engineClassName::getScraperError()) {
			$partyTally			= Riding::getPartyTally(FALSE)->getTally();
			$jurisdictionTally	= Riding::getJurisdictionTally()->getTally();
			#$partyTallyWasted	= $partyTally['wasted'];
			#arsort($partyTallyWasted);
			$summary['totalWastedVotes']	= $jurisdictionTally['wasted'];
			$magnitude			= Riding::calculateMagnitudeWinner();
		} else {
			$partyTally			= array();
			$jurisdictionTally	= array();
			$partyTallyWasted	= array();
			$summary['totalWastedVotes']	= null;
		}

	    ### Render ####################################################################
	    $response = $this->render("JlamHorkosBundle:Horkos:index.$format.twig", array(
    				'ridings'	=> Riding::getAllRdingsSorted(),
    				'partyTally' 	=> $partyTally,
    				'jurisdiction'	=> $jurisdictionTally,
    				'summary'	=> $summary,
    				'election'	=> $election ? $election : self::DEFAULT_ELECTION,
    				'error'		=> $engineClassName::getScraperError(),
    				'magnitude' => $magnitude,
    		));




        #Save into caching
        $date = new \DateTime();
        $date->modify('+'. self::CACHE_TTL_SECS .' seconds');

        $response->setPublic();
        $response->setExpires($date);
        $response->setMaxAge(self::CACHE_TTL_SECS);
        $response->setSharedMaxAge(self::CACHE_TTL_SECS);

        if($format == 'csv') {
			$response->headers->set('Content-Type', 'text/csv');
			$response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        }

		$cache->set($cacheKey, $response, self::CACHE_TTL_SECS);


		#Return the controller
        return $response;
    }


    public static function getElectionNames() {
        return array(
    		'cdn2015'	=> 'Canadian 2015 election',
    		'sk2016'	=> 'Saskatchewan 2016 election',
    		'on2018'	=> 'Ontario 2018 election',
        );
    }
    public static function getScrappingEngineClassName($electionShorthand = null) {
        $engineClassNames = array(
            'cdn2015'   => 'Cdn2015scrapper',
            'ab2015'    => 'Ab2015scrapper',
            'sk2016'    => 'Sk2016scrapper',
            'on2018'    => 'On2018scrapper',
        );

    	if(!isset($engineClassNames[$electionShorthand]))
    		$electionShorthand = self::DEFAULT_ELECTION;

    	$engineClassName =  $engineClassNames[$electionShorthand];

    	return self::BASE_DIR_SCRAPPERS . $engineClassName;
    }
}
