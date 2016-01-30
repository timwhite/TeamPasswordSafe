<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Login;
use AppBundle\Entity\User;
use AppBundle\Entity\UserGroup;
use AppBundle\Form\GroupsType;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use \Defuse\Crypto\Exception as Ex;

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

            // Generate a key for this group
            $usergroup->setGroupKey($this->encryptGroupKeyForUser($currentuser));

            $em->persist($usergroup);

            $em->flush();
            return $this->redirectToRoute('groups');
        }
        return $this->render('AppBundle:Default:newgroup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function encryptGroupKeyForUser(User $user, $groupKey = null)
    {
        if($groupKey == null)
        {
            $groupKey = $this->generateNewGroupKey();
        }

        // Encrypt key with users public key
        $pubKey = $user->getPubKey();

        // TODO check return
        openssl_public_encrypt($groupKey->saveToAsciiSafeString(), $encryptedKey, $pubKey);

        return $encryptedKey;


    }

    private function generateNewGroupKey()
    {
        /**
         * @var $key Key
         */
        try {
            $key = Crypto::createNewRandomKey();
            // WARNING: Do NOT encode $key with bin2hex() or base64_encode(),
            // they may leak the key to the attacker through side channels.
        } catch (Ex\CryptoTestFailedException $ex) {
            die('Cannot safely create a key');
        } catch (Ex\CannotPerformOperationException $ex) {
            die('Cannot safely create a key');
        }

        return $key;

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
            $group = $login->getGroup();
            // With login, we need to encrypt the password to save it
            $encryptedPassword = $this->encryptLoginWithGroupKey(
                $request,
                $group,
                $form->get('plainPassword')->getData(),
                $this->get('security.token_storage')->getToken()->getUser()
            );
            $login->setPassword($encryptedPassword);
            $em = $this->getDoctrine()->getManager();
            $em->persist($login);

            $em->flush();
            return $this->redirectToRoute('logins', ['groupid' => $group->getId()] );
        }
        return $this->render('AppBundle:Default:login.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function encryptLoginWithGroupKey(Request $request, Groups $group, $loginpass, User $user)
    {
        $groupKey = $this->getGroupKey($request, $group, $user);
        // Encrypt login with group key
        $encryptedPass = Crypto::encrypt($loginpass, $groupKey);
        return $encryptedPass;
    }

    private function decryptLoginWithGroupKey(Request $request, Groups $group, $encryptedLogin, User $user)
    {
        $groupKey = $this->getGroupKey($request, $group, $user);
        // Decrypt login with group key
        $plainPass = "";
        try {
            $plainPass = Crypto::decrypt($encryptedLogin, $groupKey);
        } catch (\Defuse\Crypto\Exception\InvalidCiphertextException $ex) { // VERY IMPORTANT
            // Either:
            //   1. The ciphertext was modified by the attacker,
            //   2. The key is wrong, or
            //   3. $ciphertext is not a valid ciphertext or was corrupted.
            // Assume the worst.
            $this->addFlash(
                'error',
                $this->get('translator')->trans('Unable to decode encrypted password - Editing now will overwrite current encrypted value')
            );
        } catch (\Defuse\Crypto\Exception\CryptoTestFailedException $ex) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'Cannot safely perform decryption - Editing now will overwrite current encrypted value'
                )
            );
        } catch (\Defuse\Crypto\Exception\CannotPerformOperationException $ex) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'Cannot safely perform decryption - Editing now will overwrite current encrypted value'
                )
            );
        }
        return $plainPass;
    }

    private function getGroupKey(Request $request, Groups $group, User $user)
    {
        // Get private key of current user
        $privKey = $request->getSession()->get('pkey');

        // Get encrypted group key
        $usergrouprepo = $this->getDoctrine()->getManager()->getRepository(UserGroup::class);
        /** @var UserGroup $usergroup */
        $usergroup = $usergrouprepo->findOneBy(
            [
                'user' => $user->getId(),
                'group' => $group->getId()
            ]
        );
        $encryptedGroupKey = $usergroup->getGroupKey();

        // Decrypt Group key with current users private key
        // TODO check return
        if (openssl_private_decrypt($encryptedGroupKey, $groupKey, $privKey)) {
            $groupKey = Key::LoadFromAsciiSafeString($groupKey);
            return $groupKey;
        } else {
            // TODO catch this upstream?
            throw new \Exception("Unable to decode group key for current user");
        }

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
        $plainPassword = $this->decryptLoginWithGroupKey(
            $request,
            $login->getGroup(),
            $login->getPassword(),
            $this->get('security.token_storage')->getToken()->getUser());
        $form->get('plainPassword')->setData($plainPassword);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // With login, we need to encrypt the password to save it
            $encryptedPassword = $this->encryptLoginWithGroupKey(
                $request,
                $login->getGroup(),
                $form->get('plainPassword')->getData(),
                $this->get('security.token_storage')->getToken()->getUser()
            );

            $login->setPassword($encryptedPassword);

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
     * @Route("/group/{groupid}/adduser/{userid}", name="add_user_group")
     */
    public function addUserGroup(Request $request, $groupid, $userid)
    {

        $em = $this->getDoctrine()->getManager();
        $groupRepo = $em->getRepository('AppBundle:Groups');
        /** @var Groups $login */
        $group = $groupRepo->findOneById($groupid);

        $this->denyAccessUnlessGranted('admin', $group);

        $userRepo = $em->getRepository('AppBundle:User');
        /** @var User $user */
        $user = $userRepo->findOneById($userid);

        if (!$user) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'Unable to find user'
                )
            );

            return $this->redirectToRoute('groups');
        }

        $userGroupRepo = $em->getRepository('AppBundle:UserGroup');
        $userGroup = $userGroupRepo->findOneBy(['user' => $user, 'group' => $group]);

        if ($userGroup) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'User is already a member of that group'
                )
            );

            return $this->redirectToRoute('groups');
        }


        $usergroup = new UserGroup();
        $usergroup->setGroup($group);
        $usergroup->setUser($user);

        // Get current group key using existing user
        $groupKey = $this->getGroupKey(
            $request,
            $usergroup->getGroup(),
            $this->get('security.token_storage')->getToken()->getUser()
        );

        // Encrypt key using the user we are adding
        $usergroup->setGroupKey($this->encryptGroupKeyForUser($user, $groupKey));
        unset($groupKey);

        $em->persist($usergroup);

        $em->flush();

        $this->addFlash(
            'success',
            $this->get('translator')->trans(
                'User added to group'
            )
        );

        return $this->redirectToRoute('groups');
    }

    /**
     * @Route("/group/{groupid}/removeuser/{userid}", name="remove_user_group")
     */
    public function removeUserGroup($groupid, $userid)
    {
        $em = $this->getDoctrine()->getManager();
        $groupRepo = $em->getRepository('AppBundle:Groups');
        /** @var Groups $login */
        $group = $groupRepo->findOneById($groupid);

        $this->denyAccessUnlessGranted('admin', $group);

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
}
