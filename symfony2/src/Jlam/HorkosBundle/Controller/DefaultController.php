<?php

namespace Jlam\HorkosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('JlamHorkosBundle:Default:index.html.twig', array('name' => $name));
    }
}
