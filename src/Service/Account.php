<?php

namespace App\Service;

use Adldap\Models\User;
use Doctrine\ORM\EntityManager;

/**
 * Class Account
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class Account
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function updateCredentials(User $adAccount, $credentials)
    {
        $accountRepository = $this->em->getRepository('App:Account');

        $account = $accountRepository->findOneBy([
            'email' => $adAccount->getEmail(),
        ]);

        if (empty($account)) {
            $account = new \App\Entity\Account();
            $account->setEmployeeId($adAccount->getEmployeeId())
                ->setAccountName($adAccount->getAccountName())
                ->setUserPrincipalName($adAccount->getUserPrincipalName())
                ->setEmail($adAccount->getEmail())
                ->setEmailContact($adAccount->getEmail())
                ->setFirstname($adAccount->getFirstName())
                ->setLastname($adAccount->getLastName())
                ->setToken($account->generateToken($account->getEmail(), $credentials))
                ->setActive(true);
        } else {
            $account->setEmployeeId($adAccount->getEmployeeId())
                ->setAccountName($adAccount->getAccountName())
                ->setUserPrincipalName($adAccount->getUserPrincipalName())
                ->setEmail($adAccount->getEmail())
                ->setEmailContact($adAccount->getEmail())
                ->setFirstname($adAccount->getFirstName())
                ->setLastname($adAccount->getLastName())
                ->setToken($account->generateToken($account->getEmail(), $credentials))
                ->setActive(true);
        }
        $this->em->persist($account);
        $this->em->flush();
    }

    public function getAccount(String $email)
    {
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);

        return $accountRepository->findByEmail($email);
    }

    public function lastLogin(String $email)
    {
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);
        $accountRepository->setLastLogin($email);
    }

    public function setGeneratedPassword(String $email, $password)
    {
        $accountRepository = $this->em->getRepository(\App\Entity\Account::class);
        $accountRepository->setGeneratedPassword($email, $password);

    }
}
