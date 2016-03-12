<?php

namespace AppBundle\Util;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Login;
use AppBundle\Util\KeyProtect;
use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception as Ex;

class FieldProtect
{
    /** @var  KeyProtect */
    protected $keyProtect;

    public function __construct(KeyProtect $keyProtect)
    {
        $this->keyProtect = $keyProtect;

    }

    /**
     * Takes a plaintext Login, encrypts it with the group key, and saves it into the login
     * @param Login $login
     * @param       $loginPassword
     * @return Login
     * @throws Ex\CannotPerformOperationException
     * @throws \Exception
     */
    public function encryptLoginPassword(Login $login, $loginPassword)
    {
        $groupKey = $this->keyProtect->getGroupKey($login->getGroup());
        // Encrypt login with group key
        return $login->setPassword(Crypto::encrypt($loginPassword, $groupKey));
    }

    public function decryptLoginPassword(Login $login)
    {
        $groupKey = $this->keyProtect->getGroupKey($login->getGroup());
        return $this->decryptProtected($groupKey, $login->getPassword());
    }

    private function decryptProtected($key, $encryptedField)
    {

        // Decrypt encrypted field with group key
        $plaintext = "";
        try {
            $plaintext = Crypto::decrypt($encryptedField, $key);
        } catch (Ex\InvalidCiphertextException $ex) { // VERY IMPORTANT
            // Either:
            //   1. The ciphertext was modified by the attacker,
            //   2. The key is wrong, or
            //   3. $ciphertext is not a valid ciphertext or was corrupted.
            // Assume the worst.
            throw $ex;
            // Unable to decode encrypted field
        } catch (Ex\CryptoTestFailedException $ex) {
            throw $ex;
            // Cannot safely perform field
        } catch (Ex\CannotPerformOperationException $ex) {
            throw $ex;
            // Cannot safely perform field
        }
        return $plaintext;
    }

}