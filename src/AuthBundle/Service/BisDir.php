<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\Configuration\ConfigurationException;
use Adldap\Configuration\DomainConfiguration;
use Adldap\Connections\Provider;
use Adldap\Models\Entry;
use Adldap\Models\User;
use Adldap\Schemas\OpenLDAP;
use BisBundle\Entity\BisPersonView;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class BisDir
 *
 * @package AuthBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class BisDir
{
    const DISABLE_ALLOWED_LIMIT = 30;

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

    /**
     * BisDir constructor.
     *
     * @param PasswordEncoderInterface $passwordEncoder
     * @param string                   $hosts
     * @param string                   $baseDn
     * @param string                   $adminUsername
     * @param string                   $adminPassword
     * @param string                   $accountSuffix
     * @param int                      $port
     * @param bool                     $followReferrals
     * @param bool                     $useTls
     * @param bool                     $useSsl
     *
     * @throws ConfigurationException
     *
     * @phpcs:disable Generic.Files.LineLength
     */
    public function __construct(PasswordEncoderInterface $passwordEncoder, string $hosts, string $baseDn, string $adminUsername, string $adminPassword, string $accountSuffix = '', int $port = 636, bool $followReferrals = true, bool $useTls = true, bool $useSsl = true)
    {
        $config = new DomainConfiguration(
            [
                'hosts' => explode(',', $hosts),
                'base_dn' => $baseDn,
                'account_suffix' => $accountSuffix,
                'username' => $adminUsername,
                'password' => $adminPassword,
                'schema' => OpenLDAP::class,
                'port' => $port,
                'version' => 3,
                'follow_referrals' => $followReferrals,
                'use_tls' => $useTls,
                'use_ssl' => $useSsl,
            ]
        );

        $adldap = new Adldap();
        $adldap->addProvider($config);

        $this->bisDir = $adldap;
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
        $user = $this->bisDir->connect()->search()->findBy('uid', $email);

        if ($user instanceof Entry) {
            return $user;
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
            ->connect()
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
        $entry = $this->bisDir->connect()->make()->entry();
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
     * Create a ldap entry from a AD user
     *
     * @param BisPersonView $bisPersonView
     *
     * @return BisDirResponse
     */
    public function createEntryFromBis(BisPersonView $bisPersonView): BisDirResponse
    {
        $entry = $this->bisDir->connect()->make()->entry();
        $entry = BisDirHelper::bisPersonViewToLdapEntry($bisPersonView, $entry);

        if ($entry->save()) {
            return new BisDirResponse(
                "User '" . $bisPersonView->getEmail() . "' successfully created in LDAP",
                BisDirResponseStatus::DONE,
                BisDirResponseType::CREATE,
                BisDirHelper::getDataBisPersonView($bisPersonView)
            );
        }

        return new BisDirResponse(
            "Unable to create this user '" . $bisPersonView->getEmail() . "' in LDAP",
            BisDirResponseStatus::FAILED,
            BisDirResponseType::CREATE,
            BisDirHelper::getDataBisPersonView($bisPersonView)
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

        if (null !== $entry) {
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
    public function synchronize(User $adAccount, String $password = null)
    {
        // Check user exist in LDAP
        $ldapUser = $this->getUser($adAccount->getEmail());

        // Logs
        $logs = [];

        // Create user in LDAP
        if (null === $ldapUser) {
            $log = $this->createEntry($adAccount);
            $logs[] = $log;
            if ($log->getStatus() === BisDirResponseStatus::DONE && null !== $password) {
                $logs[] = $this->synchronize($adAccount, $password);
            }

            return $logs;
        }

        // Update user in LDAP
        $logs[] = $this->updateUser($adAccount);
        // Move user in LDAP
        $logs[] = $this->moveUser($adAccount, $ldapUser);

        if (null !== $password) {
            // Sync password in LDAP
            $logs[] = $this->syncPassword($adAccount->getEmail(), $password);
        }

        return $logs;
    }

    /**
     * @param BisPersonView $bisPersonView
     *
     * @return BisDirResponse[]
     */
    public function synchronizeFromBis(BisPersonView $bisPersonView): array
    {
        // Check user exist in LDAP
        $ldapUser = $this->getUser($bisPersonView->getEmail());

        // Logs
        $logs = [];

        // Create user in LDAP
        if (null === $ldapUser) {
            $log = $this->createEntryFromBis($bisPersonView);
            $logs[] = $log;

            return $logs;
        }

        // Update user in LDAP
        $logs[] = $this->updateUserFromBis($bisPersonView);
        // Move user in LDAP
        $logs[] = $this->moveUserFromBis($bisPersonView, $ldapUser);

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

        if (null !== $ldapUser) {
            $entry = $this->bisDir->connect()->make()->entry();
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
     * Update basic information of a user
     *
     * @param BisPersonView $bisPersonView
     *
     * @return BisDirResponse
     */
    public function updateUserFromBis(BisPersonView $bisPersonView): BisDirResponse
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
        $ldapUser = $this->getUser($bisPersonView->getEmail());

        if (null !== $ldapUser) {
            $entry = $this->bisDir->connect()->make()->entry();
            $entry = BisDirHelper::bisPersonViewToLdapEntry($bisPersonView, $entry);
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
                        "User '" . $bisPersonView->getEmail() . "' successfully updated in LDAP",
                        BisDirResponseStatus::DONE,
                        BisDirResponseType::UPDATE,
                        BisDirHelper::getDataBisPersonView($bisPersonView, ['diff' => $diffData])
                    );
                }

                return new BisDirResponse(
                    "Unable to update user '" . $bisPersonView->getEmail() . "' in LDAP",
                    BisDirResponseStatus::FAILED,
                    BisDirResponseType::UPDATE,
                    BisDirHelper::getDataBisPersonView($bisPersonView, ['diff' => $diffData])
                );
            }

            return new BisDirResponse(
                "User '" . $bisPersonView->getEmail() . "' already up to date in LDAP",
                BisDirResponseStatus::NOTHING_TO_DO,
                BisDirResponseType::UPDATE,
                BisDirHelper::getDataBisPersonView($bisPersonView)
            );
        }

        return new BisDirResponse(
            "Unable to find a user for '" . $bisPersonView->getEmail() . "' in LDAP",
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

    /**
     * Move a user / Change DN
     *
     * @param BisPersonView $bisPersonView
     * @param Entry         $entry         The LDAP entry
     *
     * @return BisDirResponse
     */
    public function moveUserFromBis(BisPersonView $bisPersonView, Entry $entry): BisDirResponse
    {
        $rdn = 'uid=' . $bisPersonView->getEmail();
        $newParent = BisDirHelper::buildParentDn($bisPersonView->getFirstAttribute('c'));
        $oldParent = $entry->getDn();
        $oldParent = str_replace($rdn . ',', '', $oldParent);
        if ($oldParent !== $newParent) {
            // Need to move to the correct DN
            if ($entry->move($rdn, $newParent)) {
                return new BisDirResponse(
                    "DN for user '" . $bisPersonView->getEmail() . "' successfully updated in LDAP",
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
                "Unable to update the DN for user '" . $bisPersonView->getEmail() . "' in LDAP",
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
            "DN for user '" . $bisPersonView->getEmail() . "' already up to date in LDAP",
            BisDirResponseStatus::NOTHING_TO_DO,
            BisDirResponseType::MOVE,
            BisDirHelper::getDataEntry($entry)
        );
    }

    /**
     * Remove inactive user from LDAP
     *
     * @param bisPersonView[] $bisPersons List of active users
     *
     * @return BisDirResponse[]
     *
     * @throws \Adldap\Models\ModelDoesNotExistException
     */
    public function disableFromBis($bisPersons)
    {
        $userDeleted = [];
        $accountsToDisable = [];

        // Get all users from LDAP
        $ldapAccounts = $this->getAllUsers();

        // Test each ldap account against BIS
        foreach ($ldapAccounts as $ldapAccount) {
            // Get email
            $email = $ldapAccount->getFirstAttribute('mail');

            // Check Enabel email
            if (!empty($email) && (strpos($email, '@enabel.be') !== false)) {
                // User exist in bis_person_view [active users]
                if (!in_array($email, $bisPersons)) {
                    // Retrieve LDAP account by email
                    $ldapUser = $this->getUser($email);
                    if (null !== $ldapUser) {
                        $accountsToDisable[] = $ldapUser;
                    } else {
                        // User not found
                        $userDeleted[] = new BisDirResponse(
                            "Unable to find a user for '" . $email . "' in LDAP",
                            BisDirResponseStatus::EXCEPTION,
                            BisDirResponseType::DELETE
                        );
                    }
                }
            }
        }

        foreach ($accountsToDisable as $ldapUser) {
            // Collect account data for logging
            $data = BisDirHelper::getDataEntry($ldapUser);
            $email = $ldapUser->getFirstAttribute('mail');

            if (count($accountsToDisable) < self::DISABLE_ALLOWED_LIMIT) {
                // Remove account from LDAP
                if ($ldapUser->delete()) {
                    // User successfully deleted
                    $userDeleted[] = new BisDirResponse(
                        "User '" . $email . "' successfully deleted from LDAP",
                        BisDirResponseStatus::DONE,
                        BisDirResponseType::DELETE,
                        $data
                    );
                } else {
                    // User can't be deleted
                    $userDeleted[] = new BisDirResponse(
                        "Unable to delete the user '" . $email . "' in LDAP",
                        BisDirResponseStatus::FAILED,
                        BisDirResponseType::DELETE,
                        $data
                    );
                }
            } else {
                $userDeleted[] = new BisDirResponse(
                    'The amount of accounts to be deactivated exceeds the authorized limit [' . self::DISABLE_ALLOWED_LIMIT . '] !',
                    BisDirResponseStatus::EXCEPTION,
                    BisDirResponseType::DISABLE,
                    $data
                );
            }
        }

        return $userDeleted;
    }

    /**
     * Create active GO4HR user in LDAP
     *
     * @param bisPersonView[] $bisPersons List of active users
     *
     * @return BisDirResponse[]
     *
     * @throws \Adldap\Models\ModelDoesNotExistException
     */
    public function enableFromBis($bisPersons)
    {
        $userEnabled = [];

        // Test each bis_person_view user against LDAP
        foreach ($bisPersons as $bisPersonView) {
            // Get emails
            $email = $bisPersonView->getEmail();

            // Check Enabel email
            if (!empty($email) && (strpos($email, '@enabel.be') !== false)) {
                // Retrieve LDAP account by email
                $ldapUser = $this->getUser($email);
                if (null === $ldapUser) {
                    // Create LDAP account
                    $userEnabled[] = $this->createEntryFromBis($bisPersonView);
                } else {
                    // User exist !
                    $userEnabled[] = new BisDirResponse(
                        "User '" . $email . "' already in LDAP",
                        BisDirResponseStatus::NOTHING_TO_DO,
                        BisDirResponseType::CREATE,
                        BisDirHelper::getDataEntry($ldapUser)
                    );
                }
            }
        }

        return $userEnabled;
    }

    public function findAndChangeEmail(string $email, string $newEmail)
    {
        $ldapUser = $this->getUser($email);

        if (null !== $ldapUser) {
            // Change attribute
            $ldapUser
                ->setAttribute('mail', $newEmail)
                ->setAttribute('businesscategory', str_replace('@enabel.be', '@btcctb.org', $newEmail));
            $ldapUser->save();

            // Rename user to rewrite uid & dn
            $newRdn = 'uid=' . $newEmail;
            $ldapUser->rename($newRdn, null);
        }

        return $ldapUser;
    }
}
