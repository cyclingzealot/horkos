<?php

namespace Jlam\Cdn2015Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('JlamCdn2015Bundle:Default:index.html.twig', array('name' => $name));
    }
}
