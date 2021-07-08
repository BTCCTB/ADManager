<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Class SecurityAudit
 *
 * @package App\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class SecurityAudit
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function resetPassword(\App\Entity\Account $account, User $authUser)
    {
        $securityAudit = new \App\Entity\SecurityAudit();

        $securityAudit->setAction('update')
            ->setLoggedAt(new \DateTime())
            ->setObjectId($account->getObjectId())
            ->setObjectClass($account->getObjectClass())
            ->setData([
                'message' => $authUser->getIdentity() . ' asked a new password for ' . $account->getIdentity() . '',
            ])
            ->setUsername($authUser->getUserIdentifier());

        $this->em->persist($securityAudit);
        $this->em->flush();
    }

    public function changePassword(\App\Entity\Account $account, User $authUser)
    {
        $securityAudit = new \App\Entity\SecurityAudit();

        $securityAudit->setAction('update')
            ->setLoggedAt(new \DateTime())
            ->setObjectId($account->getObjectId())
            ->setObjectClass($account->getObjectClass())
            ->setData([
                'message' => $authUser->getIdentity() . ' changed the password of ' . $account->getIdentity() . '',
            ])
            ->setUsername($authUser->getUserIdentifier());

        $this->em->persist($securityAudit);
        $this->em->flush();
    }

    public function testPassword(\App\Entity\Account $account, User $authUser, bool $success)
    {
        $securityAudit = new \App\Entity\SecurityAudit();

        $securityAudit->setAction('check')
            ->setLoggedAt(new \DateTime())
            ->setObjectId($account->getObjectId())
            ->setObjectClass($account->getObjectClass())
            ->setData([
                'message' => $authUser->getIdentity() . ' tried the password of ' . $account->getIdentity() . '',
                'status' => $success,
            ])
            ->setUsername($authUser->getUserIdentifier());

        $this->em->persist($securityAudit);
        $this->em->flush();
    }
}
