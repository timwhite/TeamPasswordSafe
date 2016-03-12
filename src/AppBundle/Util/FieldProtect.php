<?php

namespace AppBundle\Util;

use AppBundle\Entity\Groups;
use AppBundle\Entity\UserGroup;
use AppBundle\Util\KeyProtect;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception as Ex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class FieldProtect
{
    /** @var Request  */
    protected $request;
    /** @var  KeyProtect */
    protected $keyProtect;

    public function __construct(RequestStack $requestStack, KeyProtect $keyProtect)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->keyProtect = $keyProtect;

    }

    public function encryptLoginWithGroupKey(Groups $group, $loginPassword)
    {
        $groupKey = $this->keyProtect->getGroupKey($group);
        // Encrypt login with group key
        $encryptedPass = Crypto::encrypt($loginPassword, $groupKey);
        return $encryptedPass;
    }

    public function decryptLoginWithGroupKey(Groups $group, $encryptedLogin)
    {

        $groupKey = $this->keyProtect->getGroupKey($group);
        // Decrypt login with group key
        $plainPass = "";
        try {
            $plainPass = Crypto::decrypt($encryptedLogin, $groupKey);
        } catch (Ex\InvalidCiphertextException $ex) { // VERY IMPORTANT
            // Either:
            //   1. The ciphertext was modified by the attacker,
            //   2. The key is wrong, or
            //   3. $ciphertext is not a valid ciphertext or was corrupted.
            // Assume the worst.
            throw $ex;
            // Unable to decode encrypted password
        } catch (Ex\CryptoTestFailedException $ex) {
            throw $ex;
            // Cannot safely perform decryption
        } catch (Ex\CannotPerformOperationException $ex) {
            throw $ex;
            // Cannot safely perform decryption
        }
        return $plainPass;
    }

}