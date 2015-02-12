<?php

namespace Jlam\Cdn2015Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use src\Jlam\Cdn2015Bundle\Cdn2015scrapper;
use Jlam\Cdn2015Bundle\Entity\Riding;
use Jlam\Cdn2015Bundle\TallyHolder;

class Cdn2015Controller extends Controller
{
    public function indexAction($name)
    {
		#If a cache is available, use it.
	
		#If not, slurp data
		Cdn2015scrapper::initialize($this->container);
		Cdn2015scrapper::scrape();
	
		#Export the data
	
	    #Render and save into cachihng
	
		#Return the controller 
        return $this->render('JlamCdn2015Bundle:Cdn2015:index.html.twig', array('data' => $data));
    }
}
