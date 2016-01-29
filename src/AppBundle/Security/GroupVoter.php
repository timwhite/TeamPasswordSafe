<?php

namespace AppBundle\Security;

use AppBundle\Entity\Groups;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class GroupVoter extends Voter
{
    const VIEW = 'view';
    const ADMIN = 'admin';

    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Groups && in_array($attribute, [
            self::VIEW,
            self::ADMIN
        ]);

    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access

            return false;
        }


        // you know $subject is a Groups object, thanks to supports
        /** @var Groups $group */
        $group = $subject;

        switch($attribute) {
            case self::VIEW:
                return $this->canView($group, $user);
            case self::ADMIN:
                return $this->canAdmin($group, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Groups $group, User $user)
    {
        /* This is an extra criteria we don't need
        // if they can edit, they can view
        if ($this->canAdmin($group, $user)) {
            return true;
        }*/

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('group', $group));

        $user_group = $user->getGroupsWithKeys()->matching($criteria);
        if (sizeof($user_group)) {
            return true;
        }

        return false;

    }

    private function canAdmin(Groups $group, User $user)
    {

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('group', $group));
        $criteria->andWhere(Criteria::expr()->eq('adminAccess', true));

        $user_group = $user->getGroupsWithKeys()->matching($criteria);

        if (sizeof($user_group)) {
            return true;
        }
        return false;
    }
}