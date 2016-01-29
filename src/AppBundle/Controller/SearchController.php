<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchController extends Controller
{
    /**
     * @Route("/search", name="liveSearch")
     */
    public function liveSearchAction(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new Response('This is not an Ajax request', 400);
        }
        $string = $request->get('searchText');
        $logins = $this->getDoctrine()
            ->getRepository('AppBundle:Login')
            ->findByLetters($string);

        $json = $this->container->get('serializer')->serialize($logins, 'json');
        return new Response($json);

    }

}