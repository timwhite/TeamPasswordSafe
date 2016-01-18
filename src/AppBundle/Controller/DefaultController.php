<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\UserGroup;
use AppBundle\Form\GroupsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/groups", name="groups")
     */
    public function showGroups() {
        $groupRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Groups');
        $groups = $groupRepo->findAll(); // TODO Make this only find groups you are a member of
        return $this->render('AppBundle:Default:groups.html.twig',
            ['groups' => $groups]
        );
    }

    /**
     * @Route("/groups/new", name="new_group")
     */
    public function createGroup(Request $request) {
        $group = new Groups();
        $form = $this->createForm(GroupsType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($group);

            // Add yourself to group
            $usergroup = new UserGroup();
            $usergroup->setGroup($group);
            $usergroup->setUser($this->get('security.token_storage')->getToken()->getUser());
            $em->persist($usergroup);

            $em->flush();
            return $this->redirectToRoute('groups');
        }
        return $this->render('AppBundle:Default:newgroup.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
