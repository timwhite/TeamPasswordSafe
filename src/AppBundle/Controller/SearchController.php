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

    /**
     * @Route("/search/usersNotInGroup", name="usersNotInGroupSearch")
     */
    public function usersNotInGroup(Request $request)
    {
        if (! $request->isXmlHttpRequest()) {
            return new Response('This is not an Ajax request', 400);
        }
        $groupid = $request->get('groupId');
        $search = $request->get('searchText');
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findByNotInGroup($groupid, $search);

        // TODO https://github.com/schmittjoh/JMSSerializerBundle/issues/100 Serializer currently exposes more than we want it to

        $json = $this->container->get('jms_serializer')->serialize($users, 'json');
        return new Response($json);

    }

}