<?php


namespace App\HorkosBundle\Controller;

#Apparently, symofny3 controllers don't extend anything
#https://symfony.com/doc/current/page_creation.html#creating-a-page-route-and-controller
#use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController
{
    public function indexAction($name)
    {
        return $this->render('AppHorkosBundle:Default:index.html.twig', array('name' => $name));
    }
}
