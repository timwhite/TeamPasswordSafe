<?php

namespace AppBundle\Serializer\Normalizer;

use AppBundle\Entity\Login;
use AppBundle\Util\FieldProtect;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LoginNormalizer implements NormalizerInterface
{
    private $fieldProtect;

    public function __construct(FieldProtect $fieldProtect)
    {
        $this->fieldProtect = $fieldProtect;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'id'     => $object->getId(),
            'name'   => $object->getName(),
            'url' => $object->getUrl(),
            'username' => $object->getUsername(),
            'password' => $this->fieldProtect->decryptLoginPassword($object),
            'notes' => $object->getNotes(),
            'group' => $object->getGroup()->getName()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Login;
    }
}

