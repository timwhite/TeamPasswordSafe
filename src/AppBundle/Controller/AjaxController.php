<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\UserGroupType;

class AjaxController extends Controller
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

        $forms = [];
        foreach($users as $user) {
            $forms[] = $this->getAddUserGroupForm($groupid, $user)->createView();
        }

        return $this->render('AppBundle:Ajax:addUserToGroup.html.twig',
            ['forms' => $forms]
        );
    }

    private function getAddUserGroupForm($groupid, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $groupRepo = $em->getRepository('AppBundle:Groups');
        /** @var Groups $login */
        $group = $groupRepo->findOneById($groupid);

        $this->denyAccessUnlessGranted('admin', $group);

        /** @var UserGroup $usergroup */
        $usergroup = new UserGroup();
        $usergroup->setUser($user);
        $usergroup->setGroup($group);

        $form = $this->createForm(UserGroupType::class, $usergroup);

        return $form;
    }

}