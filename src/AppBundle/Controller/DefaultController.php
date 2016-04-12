<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Login;
use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Form\GroupsType;
use AppBundle\Form\LoginType;
use AppBundle\Form\UserGroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use \Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('groups');
    }

    /**
     * @Route("/groups", name="groups")
     */
    public function showGroups() {
        return $this->render('AppBundle:Default:groups.html.twig',
            ['usergroups' => $this->get('security.token_storage')->getToken()->getUser()->getGroupsWithKeys()]
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);

            // Add yourself to group
            $currentuser = $this->get('security.token_storage')->getToken()->getUser();
            $usergroup = new UserGroup();
            $usergroup->setGroup($group);
            $usergroup->setUser($currentuser);
            $usergroup->setAdminAccess(true);

            $keyProtect = $this->get('appbundle.key_protect');
            // Generate a key for this group
            $usergroup->setGroupKey($keyProtect->newEncryptedGroupKeyForCurrentUser());

            $em->persist($usergroup);

            $em->flush();
            return $this->redirectToRoute('groups');
        }
        return $this->render('AppBundle:Default:newgroup.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/group/{groupid}", name="logins")
     */
    public function showLogins($groupid)
    {
        $groupRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Groups');
        $group = $groupRepo->findOneById($groupid);

        $this->denyAccessUnlessGranted('view', $group);

        if($group == null)
        {
            $this->addFlash(
                'error',
                $this->get('translator')->trans("You don't have access to this group")
            );

            return $this->redirectToRoute('groups');
        }

        return $this->render('AppBundle:Default:logins.html.twig',
            ['group' => $group]
        );
    }

    /**
     * @Route("/group/{groupid}/new", name="new_login")
     */
    public function newLogin(Request $request, $groupid)
    {
        $login = new Login();

        $groupRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Groups');
        $group = $groupRepo->findOneById($groupid);

        //$this->denyAccessUnlessGranted('view', $group);

        if($group == null || !$this->isGranted('view', $group))
        {
            $this->addFlash(
                'error',
                $this->get('translator')->trans("You don't have access to this group")
            );

            return $this->redirectToRoute('groups');
        }

        $login->setGroup($group);

        $form = $this->createForm(LoginType::class, $login, [
            'groups_repository' => $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:Groups'),
            'current_user' => $this->get('security.token_storage')->getToken()->getUser()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // With login, we need to encrypt the password to save it
            $this->get('appbundle.field_protect')->encryptLoginPassword($login, $form->get('plainPassword')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($login);

            $em->flush();
            return $this->redirectToRoute('logins', ['groupid' => $login->getGroup()->getId()] );
        }
        return $this->render('AppBundle:Default:login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login/edit/{loginid}", name="edit_login")
     */
    public function editLogin(Request $request, $loginid)
    {
        $loginRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Login');
        /** @var Login $login */
        $login = $loginRepo->findOneById($loginid);

        $this->denyAccessUnlessGranted('view', $login->getGroup());

        $form = $this->createForm(LoginType::class, $login, [
            'groups_repository' => $this->getDoctrine()->getEntityManager()->getRepository('AppBundle:Groups'),
            'current_user' => $this->get('security.token_storage')->getToken()->getUser()
        ]);

        // Current password is encrypted, lets get the plain text version
        try {
            $plainPassword = $this->get('appbundle.field_protect')->decryptLoginPassword($login);
        } catch (Ex\CryptoException $ex) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'There was a problem decoding the encrypted password - Editing now will overwrite current encrypted value'
                )
            );
            $plainPassword = "";
        }
        $form->get('plainPassword')->setData($plainPassword);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // With login, we need to encrypt the password to save it
            $this->get('appbundle.field_protect')->encryptLoginPassword($login, $form->get('plainPassword')->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($login);

            $em->flush();

            $this->addFlash(
                'success',
                $this->get('translator')->trans('Login updated')
            );

            return $this->redirectToRoute('logins', ['groupid' => $login->getGroup()->getId()] );
        }
        return $this->render('AppBundle:Default:login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/group/{id}/edit", name="edit_group")
     */
    public function editGroup(Request $request, Groups $group)
    {
        $this->denyAccessUnlessGranted('admin', $group);

        $form = $this->createForm(GroupsType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();
            return $this->redirectToRoute('groups');
        }
        return $this->render('AppBundle:Default:editgroup.html.twig', [
            'form' => $form->createView(),
            'group' => $group
        ]);

    }

    /**
     * @Route("/groupadduser", name="add_user_group")
     */
    public function addUserGroup(Request $request) {

        $usergroup = new UserGroup();
        $form = $this->createForm(UserGroupType::class, $usergroup);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();

            // If we have a public key available for the user we are adding, then we need to get the group password and encrypt it for that user
            if($usergroup->getUser()->getPubKey())
            {
                $keyProtect = $this->get('appbundle.key_protect');
                // Encrypt key using the user we are adding
                $usergroup->setGroupKey($keyProtect->encryptGroupKeyForUser($usergroup->getUser(), $usergroup->getGroup()));
                unset($groupKey);
            } else {
                throw new Exception("Missing public key for user");
            }

            $em->persist($usergroup);

            $em->flush();
            return $this->redirectToRoute('edit_group', ['id' => $usergroup->getGroup()->getId()]);
        }

        return $this->redirectToRoute('groups');
    }

    /**
     * @Route("/group/{groupid}/removeuser/{userid}", name="remove_user_group")
     * @Method({"POST"})
     */
    public function removeUserGroup(Request $request, $groupid, $userid)
    {
        $em = $this->getDoctrine()->getManager();
        $groupRepo = $em->getRepository('AppBundle:Groups');
        /** @var Groups $login */
        $group = $groupRepo->findOneById($groupid);

        $this->denyAccessUnlessGranted('admin', $group);

        if (!$this->isCsrfTokenValid('delete_user_from_group', $request->get('csrf_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }


        $userRepo = $em->getRepository('AppBundle:User');
        /** @var User $user */
        $user = $userRepo->findOneById($userid);

        if ($user == $this->get('security.token_storage')->getToken()->getUser())
        {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'Cannot remove self from group'
                )
            );

            return $this->redirectToRoute('groups');
        }

        $userGroupRepo = $em->getRepository('AppBundle:UserGroup');
        $userGroup = $userGroupRepo->findOneBy(['user' => $user, 'group' => $group]);

        if(!$userGroup) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'User is not a member of that group'
                )
            );

            return $this->redirectToRoute('groups');
        }

        $em->remove($userGroup);
        $em->flush();

        $this->addFlash(
            'success',
            $this->get('translator')->trans(
                'Removed user from group'
            )
        );
        return $this->redirectToRoute('groups');
    }

    /**
     * @Route("/export", name="export_logins")
     */
    public function exportLogins()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $loginRepo = $em->getRepository('AppBundle:Login');
        $logins = $loginRepo->findAllByUser($user);

        $serializer = $this->get('appbundle.serializer.default');

        $response = new Response($serializer->serialize($logins, 'json'));

        $response->headers->set('Content-Type', 'text/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.json"');

        return $response;


    }
}
