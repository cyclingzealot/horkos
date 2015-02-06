<?php

namespace Jlam\Cdn2015Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Cdn2015Controller extends Controller
{
    public function indexAction($name)
    {
	#If a cache is available, use it.

	#If not, slurp data

	#Produce a riding object from the data

	#Export the data

        #Render and save into cachihng

	#Return the controller 
        return $this->render('JlamCdn2015Bundle:Cdn2015:index.html.twig', array('data' => $data));
    }
}
