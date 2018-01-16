<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\Connections\Provider;
use Adldap\Models\Entry;
use Adldap\Models\User;
use AuthBundle\AuthBundle;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class BisDir
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisDir
{
    /**
     * @var Provider
     */
    private $bisDir;

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var string
     */
    private $baseDn;
    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(
        EntityManager $em,
        PasswordEncoderInterface $passwordEncoder,
        array $hosts,
        string $baseDn,
        string $adminUsername,
        string $adminPassword,
        string $accountSuffix = '',
        int $port = 389,
        bool $followReferrals = false,
        bool $useTls = false,
        bool $useSsl = false
    ) {

        $adldap = new Adldap();
        $adldap->addProvider([
            'domain_controllers' => $hosts,
            'base_dn' => $baseDn,
            'account_suffix' => $accountSuffix,
            'admin_username' => $adminUsername,
            'admin_password' => $adminPassword,
            'port' => $port,
            'version' => 3,
            'follow_referrals' => $followReferrals,
            'use_tls' => $useTls,
            'use_ssl' => $useSsl,
        ]);

        $this->bisDir = $adldap->connect();
        $this->em = $em;
        $this->baseDn = $baseDn;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Find a ldap user by email
     *
     * @param string $email His email
     *
     * @return Entry The user.
     */
    public function getUser(string $email)
    {
        $user = $this->bisDir->search()->findBy('uid', $email);

        if (!$user instanceof Entry) {
            return null;
        }

        return $user;
    }

    /**
     * Get a list of all users in ldap
     *
     * @return Entry[] The user list
     */
    public function getAllUsers(): array
    {
        return $this->bisDir
            ->search()
            ->whereEquals('objectClass', 'inetOrgPerson')
            ->get();
    }

    /**
     * Create a ldap entry from a AD user
     *
     * @param User $user The AD user
     *
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createEntry(User $user): bool
    {
        $entry = $this->bisDir->make()->entry();

        $entry->setCommonName($user->getCommonName())
            ->setDisplayName($user->getDisplayName())
            ->setAttribute('uid', $user->getEmail())
            ->setAttribute('employeeNumber', $user->getEmployeeId())
            ->setAttribute('mail', $user->getEmail())
            ->setAttribute('givenName', $user->getFirstName())
            ->setAttribute('sn', $user->getLastName())
            ->setAttribute('title', $user->getDescription())
            ->setSchema('inetOrgPerson');

        $country = $user->getCountry();

        $dn = 'uid=' . $user->getEmail();
        if (!empty($country)) {
            $dn .= ',c=' . $country;
        }
        $dn .= ',' . $this->baseDn;

        $entry->setDn($dn);

        if ($entry->save()) {
            return true;
        }

        return false;
    }

    /**
     * Synchronize password in ldap
     *
     * @param string $email The user email
     * @param string $password The clear password
     *
     * @return bool
     */
    public function syncPassword(String $email, string $password): bool
    {
        $entry = $this->getUser($email);

        if ($entry !== null) {
            $passwordEncoded = $this->passwordEncoder->encodePassword($password);
            $entry->setAttribute('userPassword', $passwordEncoded);
            if ($entry->save()) {
                return true;
            }
        }

        return false;
    }
}
