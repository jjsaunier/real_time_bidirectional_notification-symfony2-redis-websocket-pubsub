<?php

namespace Prophet777\MoonWalk\src\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/moonwalk", name="moonwalk_homepage")
     */
    public function homeAction()
    {
        $engine = $this->container->get('templating');

        return $engine->renderResponse('MoonWalkBundle::homepage.html.twig', [

        ]);
    }
}