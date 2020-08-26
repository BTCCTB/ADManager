<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserRepository
 *
 * @package App\Repository
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class UserRepository extends EntityRepository
{
    /**
     * @param String $accountName
     * @param String $newEmail
     *
     * @return null|User
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function changeEmail(String $accountName, String $newEmail)
    {
        $user = $this->findOneBy(['accountName' => $accountName]);

        if (null !== $user) {
            $user->setEmail($newEmail);
            $this->_em->persist($user);
            $this->_em->flush();
        }

        return $user;
    }

    public function syncAccount(\Adldap\Models\User $adAccount, String $password, UserInterface $user)
    {
        /** @var User $user */
        $user->eraseCredentials();
        $user
            ->setFirstname($adAccount->getFirstName())
            ->setLastname($adAccount->getLastName())
            ->setAccountName($adAccount->getAccountName())
            ->setEmail($adAccount->getEmail())
            ->setPlainPassword($password)
            ->setUpdatedAt(new DateTime())
        ;

        $this->_em->persist($user);
        $this->_em->flush();
    }
}
