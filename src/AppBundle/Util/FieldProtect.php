<?php

namespace AppBundle\Util;

use AppBundle\Entity\Groups;
use AppBundle\Entity\UserGroup;
use Defuse\Crypto\Key;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class FieldProtect
{
    /** @var Request  */
    protected $request;
    /** @var TokenStorage  */
    protected $token_storage;

    public function __construct(RequestStack $requestStack, TokenStorage $token_storage)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->token_storage = $token_storage;

    }

    private function getUser()
    {
        return $this->token_storage->getToken()->getUser();
    }

}