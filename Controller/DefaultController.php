<?php

namespace Dope\ClassifierBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('DopeClassifierBundle:Default:index.html.twig');
    }
}
