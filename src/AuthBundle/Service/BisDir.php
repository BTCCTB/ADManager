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
        int $port = 636,
        bool $followReferrals = true,
        bool $useTls = true,
        bool $useSsl = true
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
     * @return Entry|null The user.
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
            ->paginate(10000)
            ->getResults();
    }

    /**
     * Create a ldap entry from a AD user
     *
     * @param User $adAccount The AD user
     *
     * @return BisDirResponse
     */
    public function createEntry(User $adAccount): BisDirResponse
    {
        $entry = $this->bisDir->make()->entry();
        $entry = BisDirHelper::adAccountToLdapEntry($adAccount, $entry);

        if ($entry->save()) {
            return new BisDirResponse(
                "User '" . $adAccount->getEmail() . "' successfully created in LDAP",
                BisDirResponseStatus::DONE,
                BisDirResponseType::CREATE,
                BisDirHelper::getDataAdUser($adAccount)
            );
        }

        return new BisDirResponse(
            "Unable to create this user '" . $adAccount->getEmail() . "' in LDAP",
            BisDirResponseStatus::FAILED,
            BisDirResponseType::CREATE,
            BisDirHelper::getDataAdUser($adAccount)
        );
    }

    /**
     * Synchronize password in ldap
     *
     * @param string $email    The user email
     * @param string $password The clear password
     *
     * @return BisDirResponse
     */
    public function syncPassword(String $email, string $password): BisDirResponse
    {
        $entry = $this->getUser($email);

        if ($entry !== null) {
            $passwordEncoded = $this->passwordEncoder->encodePassword($password);
            $entry->setAttribute('userPassword', $passwordEncoded);
            if ($entry->save()) {
                return new BisDirResponse(
                    "Password for user '" . $entry->getFirstAttribute('mail') . "' successfully synchronized in LDAP",
                    BisDirResponseStatus::DONE,
                    BisDirResponseType::UPDATE,
                    BisDirHelper::getDataEntry($entry, ['password' => $password])
                );
            }
            return new BisDirResponse(
                "Unable to synchronize password for user '" . $email . "' in LDAP",
                BisDirResponseStatus::FAILED,
                BisDirResponseType::UPDATE,
                BisDirHelper::getDataEntry($entry, ['password' => $password])
            );
        }

        return new BisDirResponse(
            "Unable to find a user for '" . $email . "' in LDAP",
            BisDirResponseStatus::EXCEPTION,
            BisDirResponseType::UPDATE,
            [
                'email' => $email,
                'password' => $password,
            ]
        );
    }

    /**
     * @param User   $adAccount
     * @param String $password
     *
     * @return BisDirResponse[]
     */
    public function synchronize(User $adAccount, String $password): array
    {
        // Check user exist in LDAP
        $ldapUser = $this->getUser($adAccount->getEmail());

        // Logs
        $logs = [];

        // Create user in LDAP
        if ($ldapUser === null) {
            $log = $this->createEntry($adAccount);
            $logs[] = $log;
            if ($log->getStatus() === BisDirResponseStatus::DONE) {
                $logs[] = $this->synchronize($adAccount, $password);
            }
            return $logs;
        }

        // Update user in LDAP
        $logs[] = $this->updateUser($adAccount);
        // Move user in LDAP
        $logs[] = $this->moveUser($adAccount, $ldapUser);
        // Sync password in LDAP
        $logs[] = $this->syncPassword($adAccount->getEmail(), $password);

        return $logs;
    }

    /**
     * Update basic information of a user
     *
     * @param User $adAccount
     *
     * @return BisDirResponse
     */
    public function updateUser(User $adAccount): BisDirResponse
    {
        // Define the attributes that can be updated
        $attributes = [
            'title',
            'sn',
            'givenname',
            'initials',
            'businesscategory',
            'displayname',
            'cn',
        ];
        $ldapUser = $this->getUser($adAccount->getEmail());

        if ($ldapUser !== null) {
            $entry = $this->bisDir->make()->entry();
            $entry = BisDirHelper::adAccountToLdapEntry($adAccount, $entry);
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
                    return new BisDirResponse(
                        "User '" . $adAccount->getEmail() . "' successfully updated in LDAP",
                        BisDirResponseStatus::DONE,
                        BisDirResponseType::UPDATE,
                        BisDirHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                    );
                }
                return new BisDirResponse(
                    "Unable to update user '" . $adAccount->getEmail() . "' in LDAP",
                    BisDirResponseStatus::FAILED,
                    BisDirResponseType::UPDATE,
                    BisDirHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                );
            }
            return new BisDirResponse(
                "User '" . $adAccount->getEmail() . "' already up to date in LDAP",
                BisDirResponseStatus::NOTHING_TO_DO,
                BisDirResponseType::UPDATE,
                BisDirHelper::getDataAdUser($adAccount)
            );
        }
        return new BisDirResponse(
            "Unable to find a user for '" . $adAccount->getEmail() . "' in LDAP",
            BisDirResponseStatus::EXCEPTION,
            BisDirResponseType::UPDATE
        );
    }

    /**
     * Move a user / Change DN
     *
     * @param User  $adAccount The Active Directory account
     * @param Entry $entry     The LDAP entry
     *
     * @return BisDirResponse
     */
    public function moveUser(User $adAccount, Entry $entry): BisDirResponse
    {
        $rdn = 'uid=' . $adAccount->getEmail();
        $newParent = BisDirHelper::buildParentDn($adAccount->getFirstAttribute('c'));
        $oldParent = $entry->getDn();
        $oldParent = str_replace($rdn . ',', '', $oldParent);
        if ($oldParent !== $newParent) {
            // Need to move to the correct DN
            if ($entry->move($rdn, $newParent)) {
                return new BisDirResponse(
                    "DN for user '" . $adAccount->getEmail() . "' successfully updated in LDAP",
                    BisDirResponseStatus::DONE,
                    BisDirResponseType::MOVE,
                    BisDirHelper::getDataEntry(
                        $entry,
                        [
                            'from' => $oldParent,
                            'to' => $newParent,
                        ]
                    )
                );
            }

            return new BisDirResponse(
                "Unable to update the DN for user '" . $adAccount->getEmail() . "' in LDAP",
                BisDirResponseStatus::FAILED,
                BisDirResponseType::MOVE,
                BisDirHelper::getDataEntry(
                    $entry,
                    [
                        'from' => $oldParent,
                        'to' => $newParent,
                    ]
                )
            );
        }

        // Already correct, nothing to do!
        return new BisDirResponse(
            "DN for user '" . $adAccount->getEmail() . "' already up to date in LDAP",
            BisDirResponseStatus::NOTHING_TO_DO,
            BisDirResponseType::MOVE,
            BisDirHelper::getDataEntry($entry)
        );

    }
}
