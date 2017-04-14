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
	const DEFAULT_ELECTION		= 'qc2016';
	const CACHE_TTL_SECS		= 30;

    public function indexAction()
    {

    	#Get request parameters
    	$election			= $this->getRequest()->get('election')	?: self::DEFAULT_ELECTION;
    	$language			= $this->getLanguage() ?: 'en';
    	$fresh				= $this->getRequest()->get('fresh');
    	$format				= $this->getRequest()->get('format')	?: 'html';



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
        $this->get('logger')->debug("engineClassName = $engineClassName");

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
	    $viewName = "JlamHorkosBundle:Horkos:index.$language.$format.twig";
	    if ($format != 'html')
	    	$viewName = "JlamHorkosBundle:Horkos:index.$format.twig";

	    $response = $this->render($viewName, array(
    				'ridings'	=> Riding::getAllRdingsSorted(),
    				'partyTally' 	=> $partyTally,
    				'jurisdiction'	=> $jurisdictionTally,
    				'summary'	=> $summary,
    				'election'	=> $election ? $election : self::DEFAULT_ELECTION,
    				'error'		=> $engineClassName::getScraperError(),
    				'magnitude' => $magnitude,
    				'language'	=> $language,
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


    private function getLanguage() {
    	#Fist let's see if the language is set in the string
    	$requestedLang = $this->getRequest()->get('language');

    	if($requestedLang) {
    		return $requestedLang;
    	}

    	#If not, check host being requested
    	if(strpos($this->getRequest()->getHttpHost(), 'monvotedoitcompter') !== FALSE)
    		return 'fr';

    	#If not, look at the browsers setting
		$searchingFor = array('en', 'fr');

		$acceptedLanguages = $this->getRequest()->getLanguages();

		foreach ($acceptedLanguages as $acceptedLang) {
			foreach($searchingFor as $searchedLang) {
				if(explode("-", $acceptedLang)[0] == $searchedLang) {
					return $searchedLang;
				}
			}
		}
    }

    private static function getScrappingEngineClassName($electionShorthand = self::DEFAULT_ELECTION) {

    	$engineClassNames = array(
    		'cdn2015'	=> 'Cdn2015scrapper',
    		'ab2015'	=> 'Ab2015scrapper',
    		'sk2016'	=> 'Sk2016scrapper',
    		'qc2016'	=> 'Qc2016scrapper',
    	);

    	if(!isset($engineClassNames[$electionShorthand]))
    		$electionShorthand = self::DEFAULT_ELECTION;

    	$engineClassName =  $engineClassNames[$electionShorthand];

    	return self::BASE_DIR_SCRAPPERS . $engineClassName;
    }
}
