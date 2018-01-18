<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\Connections\Provider;
use Adldap\Models\Entry;
use Adldap\Models\User;
use AuthBundle\AuthBundle;
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
     * @var string
     */
    private $baseDn;
    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(
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
        $userEnabel = $this->bisDir->search()->findBy('uid', str_replace('@btcctb.org', '@enabel.be', $email));
        $userBtc = $this->bisDir->search()->findBy('uid', str_replace('@enabel.be', '@btcctb.org', $email));

        if ($user instanceof Entry) {
            return $user;
        } elseif ($userBtc instanceof Entry) {
            return $userBtc;
        } elseif ($userEnabel instanceof Entry) {
            return $userEnabel;
        }

        return null;
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
     */
    public function createEntry(User $user): bool
    {
        $entry = $this->bisDir->make()->entry();

        $entry = $this->adAccountToBisDirEntry($user, $entry);

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

    /**
     * @param User   $adAccount
     * @param String $password
     *
     * @return bool
     */
    public function synchronize(User $adAccount, String $password): bool
    {
        // Check user exist in LDAP
        $ldapUser = $this->getUser($adAccount->getEmail());

        if ($ldapUser === null) {
            // Create user in LDAP
            if ($this->createEntry($adAccount)) {
                $this->synchronize($adAccount, $password);
            }
        } else {
            if ($this->updateUser($adAccount)) {
                // Sync password in LDAP
                return $this->syncPassword($adAccount->getEmail(), $password);
            }
        }
        return false;

    }

    /**
     * Update basic information of a user
     *
     * @param User $adAccount
     *
     * @return bool
     */
    public function updateUser(User $adAccount): bool
    {
        // Define the attributes that can be updated
        $attributes = [
            'title',
            'sn',
            'givenname',
            'preferredlanguage',
            'initials',
            'businesscategory',
            'displayname',
            'cn',
        ];
        $ldapUser = $this->getUser($adAccount->getEmail());

        if ($ldapUser !== null) {
            $entry = $this->bisDir->make()->entry();
            $entry = $this->adAccountToBisDirEntry($adAccount, $entry);
            $diffData = [];
            foreach ($attributes as $attribute) {
                $value = $entry->getAttribute($attribute);
                if (\is_array($value) && array_key_exists(0, $value)) {
                    $value = $value[0];
                }
                $original = $ldapUser->getAttribute($attribute);
                if (\is_array($original) && array_key_exists(0, $original)) {
                    $original = $original[0];
                }
                if ($value !== $original) {
                    $ldapUser->setAttribute($attribute, $value);
                    $diffData[$attribute] = [
                        'attribute' => $attribute,
                        'value' => $value,
                        'original' => $original,
                    ];
                }
            }
            if (!empty($diffData)) {
                if ($ldapUser->save()) {
                    return true;
                }
            }

            return false;
        }

    }

    /**
     * Move a user / Change DN
     *
     * @param User  $adAccount The Active Directory account
     * @param Entry $entry The LDAP entry
     *
     * @return bool
     */
    public function moveUser(User $adAccount, Entry $entry): bool
    {
        //TODO: implement me!
        $country = $adAccount->getCountry();
        $dn = 'uid=' . $adAccount->getEmail();
        if (!empty($country)) {
            $dn .= ',c=' . $country;
        }
        $dn .= ',' . $this->baseDn;

        if ($entry->getDn() != $dn) {
            // Need to move to the correct DN
        } else {
            // Already correct, nothing to do!
            return true;
        }

        return false;
    }

    /**
     * Convert a Active Directory account to a LDAP entry [BisDir]
     *
     * @param User  $adAccount The Active Directory account
     * @param Entry $entry The LDAP entry
     *
     * @return Entry The LDAP entry
     */
    private function adAccountToBisDirEntry(User $adAccount, Entry $entry): Entry
    {
        //TODO: Add old btcctb.org email in one of these attribute
        $entry->setCommonName($adAccount->getCommonName())
            ->setDisplayName($adAccount->getDisplayName())
            ->setAttribute('uid', $adAccount->getEmail())
            ->setAttribute('employeenumber', $adAccount->getEmployeeId())
            ->setAttribute('mail', $adAccount->getEmail())
            ->setAttribute('businesscategory', str_replace('@enabel.be', '@btcctb.org', $adAccount->getEmail()))
            ->setAttribute('initials', $adAccount->getInitials())
            ->setAttribute('preferredlanguage', $adAccount->getAttribute('preferedLanguage')[0])
            ->setAttribute('givenname', $adAccount->getFirstName())
            ->setAttribute('sn', $adAccount->getLastName())
            ->setAttribute('title', $adAccount->getDescription())
            ->setAttribute('objectclass', 'inetOrgPerson');

        $country = $adAccount->getAttribute('c');

        $dn = 'uid=' . $adAccount->getEmail();
        if (!empty($country)) {
            $dn .= ',c=' . $country[0];
        }
        $dn .= ',' . $this->baseDn;

        $entry->setDn($dn);

        return $entry;
    }
}
