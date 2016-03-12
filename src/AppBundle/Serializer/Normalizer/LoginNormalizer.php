<?php

namespace AppBundle\Serializer\Normalizer;

use AppBundle\Entity\Login;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LoginNormalizer implements NormalizerInterface
{
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
            'password' => $object->getPassword(),
            'notes' => $object->getNotes(),
            'groups' => $object->getGroup()->getName()
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

