<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Login;
use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\UserGroupType;
use Hackzilla\PasswordGenerator\Generator\ComputerPasswordGenerator;
use Hackzilla\PasswordGenerator\Generator\HumanPasswordGenerator;


class AjaxController extends Controller
{
    /**
     * @Route("/search", name="liveSearch", condition="request.isXmlHttpRequest()")
     */
    public function liveSearchAction(Request $request)
    {
        $string = $request->get('searchText');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $logins = $this->getDoctrine()
            ->getRepository('AppBundle:Login')
            ->findByLetters($string, $user);

        $json = $this->container->get('serializer')->serialize($logins, 'json');
        return new Response($json);

    }

    /**
     *
     * @Route("/login/{id}/password", name="ajaxPassword", condition="request.isXmlHttpRequest()")
     */
    public function loginRetrievePassword(Login $login)
    {
        $fieldProtect = $this->get('appbundle.field_protect');
        return new JsonResponse($fieldProtect->decryptLoginPassword($login));
    }

    /**
     * @Route("/search/usersNotInGroup", name="usersNotInGroupSearch", options={"expose"=true})
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

    /**
     * @Route("/generate/humanPassword", name="generateHumanPassword", condition="request.isXmlHttpRequest()")
     */
    public function generatePasswordAction(Request $request)
    {
        $generator = new ComputerPasswordGenerator();

        $generator
            ->setUppercase()
            ->setLowercase()
            ->setNumbers()
            ->setSymbols(false)
            ->setAvoidSimilar()
            ->setLength(12);

        $passwords = $generator->generatePasswords(5);

        $generator = new HumanPasswordGenerator();
        $generator
            ->setWordList('/usr/share/dict/words')
            ->setWordCount(3)
            ->setWordSeparator('-');

        $passwords = array_merge($passwords, $generator->generatePasswords(5));
        //return new JsonResponse($passwords);

        return $this->render('AppBundle:Ajax:generatePasswords.html.twig',
            ['passwords' => $passwords]
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