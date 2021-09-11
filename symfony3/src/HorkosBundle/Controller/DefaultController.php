<?php

namespace App\HorkosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AppHorkosBundle:Default:index.html.twig', array('name' => $name));
    }
}
