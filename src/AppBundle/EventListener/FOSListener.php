<?php
namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class FOSListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            //FOSUserEvents::SECURITY_IMPLICIT_LOGIN => 'onImplicitLogin',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChange',
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistration',
        );
    }


    public function onRegistration(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getData();
        $this->generateKeyPair($user, $user->getPlainPassword());
    }
    /*public function onImplicitLogin(UserEvent $event)
    {
        //$user = $event->getUser();
        //$this->generateKeyPair($user);
        //$user->setLastLogin(new \DateTime());
        //$this->userManager->updateUser($user);
    }*/

    public function onPasswordChange(FormEvent $event)
    {
        // Get new password
        /** @var User $user */
        $user = $event->getForm()->getData();
        $password = $user->getPlainPassword();
        // Get pkey from session
        $privKey = $event->getRequest()->getSession()->get('pkey');
        // Secure pkey with new password
        $res = openssl_pkey_get_private($privKey);
        openssl_pkey_export($res, $privKey, $password);
        // Store pkey in user
        $user->setPrivateKey($privKey);

        unset($password);
        openssl_pkey_free($res);
        unset($privKey);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $password = $event->getRequest()->get('_password');
        if ($user instanceof UserInterface) {
            $this->generateKeyPair($user, $password);

            $event->getRequest()->getSession()->set('pkey', $this->getPrivateKey($user, $password));

        }
        unset($password);
    }

    private function getPrivateKey(User $user, $password)
    {
        $res = openssl_pkey_get_private($user->getPrivateKey(), $password);
        openssl_pkey_export($res, $privKey);
        return $privKey;
    }

    private function generateKeyPair(User $user, $password)
    {
        if($user->getPubKey() == null) {
            $config = array(
                "digest_alg" => "sha512",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            // Create the keypair
            $res = openssl_pkey_new($config);

            // Get private key
            openssl_pkey_export($res, $privkey, $password);

            // Get public key
            $pubkey = openssl_pkey_get_details($res);
            $pubkey = $pubkey["key"];
            $user->setPrivateKey($privkey);
            $user->setPubKey($pubkey);
            //$this->userManager->updateUser($user);
        }
    }
}