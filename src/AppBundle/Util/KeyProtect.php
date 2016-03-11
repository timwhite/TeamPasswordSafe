<?php

namespace AppBundle\Util;

use AppBundle\Entity\Groups;
use AppBundle\Entity\UserGroup;
use AppBundle\Repository\UserGroupRepository;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class KeyProtect
{
    /** @var Request  */
    protected $request;
    /** @var TokenStorage  */
    protected $token_storage;

    /** @var  UserGroupRepository */
    protected $userGroupRepository;

    public function __construct(RequestStack $requestStack, TokenStorage $token_storage, UserGroupRepository $userGroupRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->token_storage = $token_storage;
        $this->userGroupRepository = $userGroupRepository;

    }

    private function getUser()
    {
        return $this->token_storage->getToken()->getUser();
    }

    public function getGroupKey(Groups $group)
    {
        // Get private key of current user
        $privKey = $this->request->getSession()->get('pkey');

        // Get encrypted group key
        /** @var UserGroup $usergroup */
        $usergroup = $this->userGroupRepository->findOneBy(
            [
                'user' => $this->getUser()->getId(),
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

    public function encryptGroupKeyForCurrentUser($groupKey = null)
    {
        if($groupKey == null)
        {
            $groupKey = $this->generateNewGroupKey();
        }

        // Encrypt key with users public key
        $pubKey = $this->getUser()->getPubKey();

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

}