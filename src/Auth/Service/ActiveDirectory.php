<?php

namespace Auth\Service;

use Adldap\Adldap;
use Adldap\AdldapException;
use Adldap\Auth\BindException;
use Adldap\Auth\PasswordRequiredException;
use Adldap\Auth\UsernameRequiredException;
use Adldap\Configuration\DomainConfiguration;
use Adldap\Models\Attributes\AccountControl;
use Adldap\Models\Attributes\DistinguishedName;
use Adldap\Models\OrganizationalUnit;
use Adldap\Models\User;
use Adldap\Query\Collection;
use Adldap\Schemas\ActiveDirectory as AdldapActiveDirectory;
use App\Entity\UserLanguage;
use App\Service\Account;
use Bis\Entity\BisContractSf;
use Bis\Entity\BisCountry;
use Bis\Entity\BisPersonSf;
use Bis\Entity\BisPersonView;
use Bis\Repository\BisPersonSfRepository;
use Bis\Repository\BisPersonViewRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Debug\Exception\ContextErrorException;

/**
 * Class ActiveDirectory
 *
 * @package Auth\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ActiveDirectory
{
    const DISABLE_ALLOWED_LIMIT = 30;

    /**
     * @var Adldap
     */
    private $activeDirectory;

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var string
     */
    private $baseDn;
    /**
     * @var Account
     */
    private $accountService;

    /**
     * @var BisDir
     */
    private $bisDir;

    /**
     * @var EntityManager
     */
    private $bis;

    /**
     * ActiveDirectory constructor.
     *
     * @param EntityManager $em
     * @param Account       $accountService
     * @param BisDir        $bisDir
     * @param string        $hosts
     * @param string        $baseDn
     * @param string        $adminUsername
     * @param string        $adminPassword
     * @param string        $accountSuffix
     * @param int           $port
     * @param bool          $followReferrals
     * @param bool          $useTls
     * @param bool          $useSsl
     *
     * @throws \Adldap\Configuration\ConfigurationException
     *
     * @phpcs:disable Generic.Files.LineLength
     */
    public function __construct(EntityManager $em, EntityManager $bis, Account $accountService, BisDir $bisDir, string $hosts, string $baseDn, string $adminUsername, string $adminPassword, string $accountSuffix = '', int $port = 636, bool $followReferrals = false, bool $useTls = true, bool $useSsl = true)
    {
        $config = new DomainConfiguration(
            [
                'hosts' => explode(',', $hosts),
                'base_dn' => $baseDn,
                'account_suffix' => $accountSuffix,
                'username' => $adminUsername,
                'password' => $adminPassword,
                'schema' => AdldapActiveDirectory::class,
                'port' => $port,
                'version' => 3,
                'timeout' => 50,
                'follow_referrals' => $followReferrals,
                'use_tls' => $useTls,
                'use_ssl' => $useSsl,
            ]
        );

        $adldap = new Adldap();
        $adldap->addProvider($config);

        $this->activeDirectory = $adldap;
        $this->em = $em;
        $this->baseDn = $baseDn;
        $this->accountService = $accountService;
        $this->bisDir = $bisDir;
        $this->bis = $bis;
    }

    /**
     * Find a active directory user by email
     *
     * @param string $email His email
     *
     * @return User|null The user.
     */
    public function getUser(string $email)
    {
        $user = $this->checkUserExistByEmail($email);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    /**
     * Find a active directory user by employee ID
     *
     * @param int $employeeID His employee ID
     *
     * @return User|null The user.
     */
    public function getUserByEmployeeId(int $employeeID)
    {
        $user = $this->checkUserExistByEmployeeID($employeeID);

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }

    /**
     * Check credentials for a user by email
     *
     * @param string $email    His email
     * @param string $password His password
     *
     * @return bool Returns true if the credentials are valid.
     *
     * @throws BindException
     * @throws PasswordRequiredException
     * @throws UsernameRequiredException
     */
    public function checkCredentials(string $email, string $password): bool
    {
        $user = $this->getUser($email);

        return $this->activeDirectory->connect()->auth()->attempt($user->getUserPrincipalName(), $password);
    }

    /**
     * Change user password
     *
     * @param string $email    His email
     * @param string $password His password
     *
     * @return bool Returns true if the password is updated.
     *
     * @throws AdldapException
     */
    public function changePassword(string $email, string $password): bool
    {
        $user = $this->getUser($email);

        if (true === ActiveDirectoryHelper::checkPasswordComplexity($password)) {
            $user->setPassword($password);
            $this->accountService->updateCredentials($user, $password);
            $this->bisDir->syncPassword($email, $password);

            return $user->save();
        }

        return false;
    }

    /**
     * Check if user exist in AD
     *
     * @param String $username The username [firstname.lastname]
     *
     * @return bool|User
     */
    public function checkUserExistByUsername($username)
    {
        $user = $this->activeDirectory->connect()->search()->findBy('userprincipalname', $username);

        if ($user instanceof User) {
            return $user;
        }

        return false;
    }

    /**
     * Check if user exist in AD
     *
     * @param String $email The email [firstname.lastname@company.domain]
     *
     * @return bool|User
     */
    public function checkUserExistByEmail($email)
    {
        $user = $this->activeDirectory->connect()->search()->findBy('mail', $email);

        if ($user instanceof User) {
            return $user;
        }

        return false;
    }

    /**
     * Check if user exist in AD
     *
     * @param integer $employeeID The employee ID [BIS ID]
     *
     * @return bool|User
     */
    public function checkUserExistByEmployeeID($employeeID)
    {
        $user = $this->activeDirectory->connect()->search()->findBy('employeeid', $employeeID);

        if ($user instanceof User) {
            return $user;
        }

        return false;
    }

    /**
     * Get all users from Active Directory
     *
     * @param string $sortField     The specified field to sort
     * @param string $sortDirection The specified direction to sort
     *
     * @return User[]|Collection<User>
     */
    public function getAllUsers($sortField, $sortDirection = 'ASC')
    {
        $users = new Collection();

        $users = $users->merge($this->getHqUsers($sortField, $sortDirection));
        $users = $users->merge($this->getFieldUsers($sortField, $sortDirection));

        return $users;
    }

    /**
     * Get all field users from Active Directory
     *
     * @param string $sortField     The specified field to sort
     * @param string $sortDirection The specified direction to sort
     *
     * @return User[]|Collection<User>
     */
    public function getFieldUsers($sortField = 'cn', $sortDirection = 'ASC')
    {
        $fieldDn = new DistinguishedName();
        $fieldDn->addDc('ad4dev')->addDc('local');
        $fieldDn->addOu('Enabel-World');

        return $this->activeDirectory->connect()
            ->search()
            ->users()
            ->whereEquals('objectClass', 'user')
            ->in($fieldDn->get())
            ->sortBy($sortField, $sortDirection)
            ->paginate(10000)
            ->getResults();
    }

    /**
     * Get all hq users from Active Directory
     *
     * @param string $sortField     The specified field to sort
     * @param string $sortDirection The specified direction to sort
     *
     * @return User[]|Collection<User>
     */
    public function getHqUsers($sortField = 'cn', $sortDirection = 'ASC')
    {
        $hqDn = new DistinguishedName();
        $hqDn->addDc('ad4dev')->addDc('local');
        $hqDn->addOu('Users')->addOu('Enabel-Brussels');

        return $this->activeDirectory->connect()
            ->search()
            ->users()
            ->whereEquals('objectClass', 'user')
            ->in($hqDn->get())
            ->sortBy($sortField, $sortDirection)
            ->paginate(10000)
            ->getResults();
    }

    /**
     * Disable a AD account
     *
     * @param User $user The user
     *
     * @return bool
     */
    public function disableUser(User $user): bool
    {
        if (!empty($user)) {
            $user->setUserAccountControl(AccountControl::ACCOUNTDISABLE);
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable a AD account
     *
     * @param User $user The user
     *
     * @return bool
     */
    public function enableUser(User $user): bool
    {
        if (!empty($user)) {
            if ($user->getCountry() !== 'BE') {
                $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT | AccountControl::DONT_EXPIRE_PASSWORD);
            } else {
                $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            }
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if OU exist in AD
     *
     * @param String $name       The name of the OU
     * @param bool   $autoCreate Create the OU if not exist
     *
     * @return OrganizationalUnit|false
     */
    public function checkOuExistByName($name, $autoCreate = false)
    {
        if ('BEL' === $name) {
            $name = 'Users';
        }

        $organizationalUnit = $this->activeDirectory->connect()->search()->ous()->findBy('name', $name);

        if ($organizationalUnit instanceof OrganizationalUnit) {
            return $organizationalUnit;
        }
        if ($autoCreate) {
            $this->createCountryOu($name);

            return $this->checkOuExistByName($name);
        }

        return false;
    }

    /**
     * Check if user is member of a specific organizational unit
     *
     * @param User               $user The user
     * @param OrganizationalUnit $ou   The organizational unit
     *
     * @return bool
     */
    public function isUserMemberOf(User $user, OrganizationalUnit $ou): bool
    {
        /**
         * @var User[] $members
         */
        $members = $this->activeDirectory->connect()->search()->users()->setDn($ou->getDn())->paginate(10000)->getResults();
        foreach ($members as $member) {
            if ($user->getDn() === $member->getDn()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return OrganizationalUnit|bool
     */
    public function createCountryOu($name)
    {
        $ou = $this->activeDirectory->connect()->make()->ou();
        $ou->setName($name);

        $dn = new DistinguishedName();
        $dn->setBase($this->baseDn);
        $dn->addOu($name);
        $dn->addOu('Enabel-World');
        $ou->setDn($dn->get());

        // Save the OU.
        if ($ou->save()) {
            return $ou;
        }

        return false;
    }

    /**
     * @param string $country
     *
     * @return ActiveDirectoryResponse[]
     *
     * @throws AdldapException
     */
    public function cronTaskSynchronize($country = null)
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // ActiveDirectoryResponse Logs
        $logs = [];

        // Get BisPersonView Repository
        /** @var BisPersonViewRepository $bisPersonView */
        $bisPersonView = $this->bis->getRepository(BisPersonView::class);

        // Get active users in GO4HR
        if (!empty($country)) {
            $bisUsers = $bisPersonView->findBy(['perCountryWorkplace' => $country]);
        } else {
            $bisUsers = $bisPersonView->findAllWithMail();
        }

        foreach ($bisUsers as $bisUser) {
            $adAccount = $this->checkUserExistByUsername($bisUser->getDomainAccount());

            if ($adAccount instanceof User) {
                // User is active?
                if ($adAccount->isDisabled()) {
                    $logs[] = $this->enableExistingAccount($adAccount);
                }
                $logs[] = $this->updateAccount($bisUser->getDomainAccount());
                // Move this user in correct OU
                $moved = $this->userNeedToMove($bisUser, $adAccount);
                $logs[] = $moved;
                if (ActiveDirectoryResponseStatus::ACTION_NEEDED === $moved->getStatus()) {
                    $logs[] = $this->moveUser($bisUser, $adAccount);
                }
                $logs[] = $this->renameAdUser($bisUser->getDomainAccount());
            } else {
                $logs[] = $this->createUser($bisUser);
            }
        }

        // Find inactive user
        $adUsers = $this->getAllUsers('email', 'ASC');
        $accountsToDisable = [];
        foreach ($adUsers as $adUser) {
            $bisUser = null;
            if (!empty($adUser->getEmail())) {
                $bisUser = $bisPersonView->getUserByEmail($adUser->getEmail());
            }
            if (empty($bisUser)) {
                if ($adUser->getFirstAttribute('importedFrom') !== 'AD-ONLY') {
                    $accountsToDisable[] = $adUser;
                }
            }
        }

        foreach ($accountsToDisable as $adAccount) {
            if (count($accountsToDisable) < self::DISABLE_ALLOWED_LIMIT) {
                $logs[] = $this->disableAccount($adAccount);
            } else {
                $logs[] = new ActiveDirectoryResponse(
                    'The amount of accounts to be deactivated exceeds the authorized limit [' . self::DISABLE_ALLOWED_LIMIT . '] !',
                    ActiveDirectoryResponseStatus::EXCEPTION,
                    ActiveDirectoryResponseType::DISABLE,
                    ActiveDirectoryHelper::getDataAdUser($adAccount)
                );
            }
        }

        return $logs;
    }

    public function ghostAccount()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // ActiveDirectoryResponse Logs
        $logs = [];

        // Get BisPersonView Repository
        /** @var BisPersonViewRepository $bisPersonView */
        $bisPersonView = $this->bis->getRepository(BisPersonView::class);
        /** @var BisPersonSfRepository $bisPersonSf */
        $bisPersonSf = $this->bis->getRepository(BisPersonSf::class);
        /** @var BisPersonSfRepository $bisContractSf */
        $bisContractSf = $this->bis->getRepository(BisContractSf::class);

        $adUsers = $this->getAllUsers('email', 'ASC');
        foreach ($adUsers as $adUser) {
            $bisUser = null;
            if (!empty($adUser->getEmail())) {
                /** @var BisPersonView $bisUser */
                $bisUser = $bisPersonView->getUserByEmail($adUser->getEmail());
            }
            if (empty($bisUser)) {
                if ($adUser->getPhysicalDeliveryOfficeName() !== 'AD-ONLY') {
                    /** @var BisPersonSf $userData */
                    $userData = $bisPersonSf->findOneBy(['perEmail' => $adUser->getEmail()]);
                    $log = [
                        'email' => $adUser->getEmail(),
                        'account' => $adUser->getAccountName(),
                        'dn' => $adUser->getDn(),
                        'startDate' => '',
                        'endDate' => '',
                        'log' => 'User does not exist in GO4HR',
                    ];

                    if (!empty($userData)) {
                        /** @var BisContractSf $contractData */
                        $contractData = $bisContractSf->findOneBy(['conPerId' => $userData->getPerId()], ['conDateStart' => 'DESC']);
                        $log['log'] = 'No contract information';
                        if (!empty($contractData)) {
                            $log['startDate'] = $contractData->getConDateStart()->format('Y-m-d');
                            $log['endDate'] = $contractData->getConDateStop()->format('Y-m-d');
                            $log['log'] = 'Contract terminated';
                        }
                    }
                    $logs[] = $log;
                }
            }
        }

        return $logs;
    }

    /**
     * Check user need to be moved
     *
     * @param BisPersonView $bisUser
     * @param User          $adAccount
     *
     * @return ActiveDirectoryResponse
     */
    public function userNeedToMove(BisPersonView $bisUser, User $adAccount): ActiveDirectoryResponse
    {
        // Check Organizational Unit exist
        $organizationalUnit = $this->checkOuExistByName($bisUser->getCountry());

        if (false === $organizationalUnit) {
            return new ActiveDirectoryResponse(
                "Organizational unit for country '" . $bisUser->getCountry() . "' doesn't exist!",
                ActiveDirectoryResponseStatus::EXCEPTION,
                ActiveDirectoryResponseType::MOVE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        }

        if (!$this->isUserMemberOf($adAccount, $organizationalUnit)) {
            return new ActiveDirectoryResponse(
                "User '" . $bisUser->getEmail() . "' need to be moved to organizational unit '" . $organizationalUnit->getDn() . "'",
                ActiveDirectoryResponseStatus::ACTION_NEEDED,
                ActiveDirectoryResponseType::MOVE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $bisUser->getEmail() . "' already in correct organizational unit",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::MOVE
        );
    }

    /**
     * Move user in the correct Organizational Unit
     *
     * @param BisPersonView $bisUser
     * @param User          $adAccount
     *
     * @return ActiveDirectoryResponse
     */
    public function moveUser(BisPersonView $bisUser, User $adAccount): ActiveDirectoryResponse
    {
        // Check Organizational Unit exist
        $organizationalUnit = $this->checkOuExistByName($bisUser->getCountry());

        if (false === $organizationalUnit) {
            return new ActiveDirectoryResponse(
                "Organizational unit for country '" . $bisUser->getCountry() . "' doesn't exist!",
                ActiveDirectoryResponseStatus::EXCEPTION,
                ActiveDirectoryResponseType::MOVE,
                ActiveDirectoryHelper::getDataAdUser(
                    $adAccount,
                    [
                        'from' => $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName()),
                        'to' => $bisUser->getCountry(),
                    ]
                )
            );
        }
        //$rdn = 'CN=' . $adAccount->getCommonName();
        $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
        if (!$adAccount->move($organizationalUnit->getDn())) {
            $from = implode('/', array_reverse($oldOu->getComponents('ou')));
            $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->getComponents('ou')));

            return new ActiveDirectoryResponse(
                "User '" . $bisUser->getEmail() . "' can't be moved from '" . $from . "' to '" . $to . "'",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::MOVE,
                ActiveDirectoryHelper::getDataAdUser(
                    $adAccount,
                    [
                        'from' => $oldOu->get(),
                        'to' => $organizationalUnit->getDn(),
                    ]
                )
            );
        }
        $from = implode('/', array_reverse($oldOu->getComponents('ou')));
        $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->getComponents('ou')));

        return new ActiveDirectoryResponse(
            "User '" . $bisUser->getEmail() . "' moved from '" . $from . "' to '" . $to . "'",
            ActiveDirectoryResponseStatus::DONE,
            ActiveDirectoryResponseType::MOVE,
            ActiveDirectoryHelper::getDataAdUser(
                $adAccount,
                [
                    'from' => $oldOu->get(),
                    'to' => $organizationalUnit->getDn(),
                ]
            )
        );
    }

    public function updateAccount(string $email): ActiveDirectoryResponse
    {
        $adAccount = $this->checkUserExistByUsername($email);
        /** @var BisPersonViewRepository $bisPersonViewRepo */
        $bisPersonViewRepo = $this->bis->getRepository(BisPersonView::class);
        $bisUser = $bisPersonViewRepo->getUserByEmail($email);

        if (false !== $adAccount && !empty($bisUser)) {
            // Check if a language preference exist for this user

            $userLanguage = $this->em->getRepository(UserLanguage::class)->findOneBy(['userId'=>$bisUser->getEmployeeId()]);
            if (null !== $userLanguage) {
                $bisUser->setLanguage($userLanguage->getShortLanguage());
            }

            // Set BIS data in Active Directory format
            [$adAccount, $diffData] = ActiveDirectoryHelper::bisPersonUpdateAdUser($bisUser, $adAccount);
            //$adAccount->setAccountName($original['samaccountname'][0]);

            if (!empty($diffData)) {
                if ($adAccount->save()) {
                    $this->bisDir->synchronize($adAccount);

                    return new ActiveDirectoryResponse(
                        "User '" . $adAccount->getEmail() . "' successfully updated in Active Directory",
                        ActiveDirectoryResponseStatus::DONE,
                        ActiveDirectoryResponseType::UPDATE,
                        ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                    );
                }

                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' can't be disabled in Active Directory",
                    ActiveDirectoryResponseStatus::FAILED,
                    ActiveDirectoryResponseType::UPDATE,
                    ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' already up to date in Active Directory",
                ActiveDirectoryResponseStatus::NOTHING_TO_DO,
                ActiveDirectoryResponseType::UPDATE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $email . "' not found! " . ((false === $adAccount) ? '[NOT IN AD]' : '') . ((null === $bisUser) ? '[NOT IN BIS]' : ''),
            ActiveDirectoryResponseStatus::EXCEPTION,
            ActiveDirectoryResponseType::UPDATE,
            [
                'AD data' => (array) $adAccount,
                'BIS data' => (array) $bisUser,
            ]
        );
    }

    public function renameAdUser(string $email)
    {
        $adAccount = $this->checkUserExistByUsername($email);
        /** @var BisPersonViewRepository $bisPersonViewRepo */
        $bisPersonViewRepo = $this->bis->getRepository(BisPersonView::class);
        $bisUser = $bisPersonViewRepo->getUserByEmail($email);

        if (false !== $adAccount && !empty($bisUser)) {
            if ($bisUser->getCommonName() !== $adAccount->getCommonName()) {
                // Change dn based on CommonName
                $newDn = $adAccount->getDnBuilder();
                $newDn->removeCn($adAccount->getCommonName());
                $newDn->addCn($bisUser->getCommonName());
                $oldDn = $adAccount->getDnBuilder()->get();

                //$rdn = 'CN=' . $bisUser->getCommonName();
                $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
                if (!$adAccount->move($oldOu->get())) {
                    return new ActiveDirectoryResponse(
                        "Unable to rename user '" . $adAccount->getEmail() . "'!",
                        ActiveDirectoryResponseStatus::EXCEPTION,
                        ActiveDirectoryResponseType::UPDATE,
                        ActiveDirectoryHelper::getDataAdUser(
                            $adAccount,
                            [
                                'to' => $newDn->get(),
                                'from' => $oldDn,
                            ]
                        )
                    );
                }
                $this->bisDir->synchronize($adAccount);

                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' successfully updated in Active Directory",
                    ActiveDirectoryResponseStatus::EXCEPTION,
                    ActiveDirectoryResponseType::UPDATE,
                    ActiveDirectoryHelper::getDataAdUser(
                        $adAccount,
                        [
                            'to' => $newDn->get(),
                            'from' => $oldDn,
                        ]
                    )
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' already up to date in Active Directory",
                ActiveDirectoryResponseStatus::NOTHING_TO_DO,
                ActiveDirectoryResponseType::UPDATE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $email . "' not found! " . ((false === $adAccount) ? '[NOT IN AD]' : '') . ((null === $bisUser) ? '[NOT IN BIS]' : ''),
            ActiveDirectoryResponseStatus::EXCEPTION,
            ActiveDirectoryResponseType::UPDATE,
            [
                'AD data' => (array) $adAccount,
                'BIS data' => (array) $bisUser,
            ]
        );
    }

    /**
     * Initialize a account with generated password.
     *
     * @param User $fieldUser
     *
     * @return ActiveDirectoryResponse
     *
     * @throws AdldapException
     */
    public function initAccount(User $fieldUser)
    {
        // Generate un new password
        $password = ActiveDirectoryHelper::generatePassword();

        if (!empty($fieldUser->getEmail())) {
            // Set the generated password
            $fieldUser->setPassword($password);
            $this->accountService->updateCredentials($fieldUser, $password);
            $this->accountService->setGeneratedPassword($fieldUser->getEmail(), $password);

            // Set default UserControl
            if ($fieldUser->getCountry() !== 'BE') {
                $fieldUser->setUserAccountControl(
                    AccountControl::NORMAL_ACCOUNT | AccountControl::DONT_EXPIRE_PASSWORD
                );
            } else {
                $fieldUser->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            }

            // Set email to correct account
            $fieldUser->setEmail(str_replace('@btcctb.org', '@enabel.be', $fieldUser->getEmail()));

            // Save all these modification
            if ($fieldUser->save()) {
                // Send a ActiveDirectoryResponse
                return new ActiveDirectoryResponse(
                    "Account '" . $fieldUser->getUserPrincipalName() . "' successfully initialized!",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::CREATE,
                    ActiveDirectoryHelper::getDataAdUser(
                        $fieldUser,
                        [
                            'password' => $password,
                            'generatedpassword' => $password,
                            'fullname' => $fieldUser->getCommonName(),
                            'domainaccount' => $fieldUser->getUserPrincipalName(),
                        ]
                    )
                );
            }
        }
        return new ActiveDirectoryResponse(
            "Account '" . $fieldUser->getUserPrincipalName() . "' can not be initialized!",
            ActiveDirectoryResponseStatus::FAILED,
            ActiveDirectoryResponseType::CREATE,
            ActiveDirectoryHelper::getDataAdUser(
                $fieldUser,
                [
                    'password' => $password,
                ]
            )
        );
    }

    public function migrate()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Error Log
        $logs = [];

        /**
         * @var User[] $users
         */
        $users = $this->getAllUsers('mail');

        foreach ($users as $user) {
            $email = strtolower($user->getEmail());
            $state = '<comment>OK</comment>';
            if (!empty($email)) {
                $emailPart = explode('@', $email);
                if ($user->getEmail() !== $emailPart[0] . '@enabel.be') {
                    $user->setEmail($emailPart[0] . '@enabel.be');
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $email,
                        'new' => $emailPart[0] . '@enabel.be',
                        'state' => $state,
                    ];
                }
            }
        }

        return $logs;
    }

    /**
     * Synchronise a BisPersonView with Active directory
     *
     * @param BisPersonView $bisPersonView
     *
     * @return ActiveDirectoryResponse[]
     *
     * @throws AdldapException
     */
    public function synchronizeFromBis(BisPersonView $bisPersonView)
    {
        // Check user exist in AD
        $adAccount = $this->getUser($bisPersonView->getEmail());

        // Logs
        $logs = [];

        // Create user in AD
        if (null === $adAccount) {
            $log = $this->createUser($bisPersonView);
            $logs[] = $log;

            return $logs;
        }

        // Update user in AD
        $logs[] = $this->updateAccount($bisPersonView->getEmail());
        $moved = $this->userNeedToMove($bisPersonView, $adAccount);
        if (ActiveDirectoryResponseStatus::ACTION_NEEDED === $moved->getStatus()) {
            // Move user in AD
            $logs[] = $this->moveUser($bisPersonView, $adAccount);
        }

        return $logs;
    }

    /**
     * Create a external user  with Active directory
     *
     * @return ActiveDirectoryResponse
     *
     * @throws AdldapException
     */
    public function createExternal(array $data)
    {
        // Check user exist in AD
        $adAccount = $this->getUser($data['login']);

        // Create user in AD
        if (null === $adAccount) {
            // Init a new Active Directory user
            $user = $this->activeDirectory->connect()->make()->user();
            // Get the correct organizational unit
            $organizationalUnit = $this->checkOuExistByName('AD-ONLY');

            // Set user data in Active Directory format
            $user->setEmployeeId(time());
            $user->setCommonName(ucfirst($data['firstname']) . ' ' . strtoupper($data['lastname']));
            $user->setAccountName(substr($data['firstname'], 0, 1) . substr($data['lastname'], 0, 7) . '_ext');
            $user->setDisplayName(ucfirst($data['firstname']) . ', ' . strtoupper($data['lastname']));
            $user->setFirstName(ucfirst($data['firstname']));
            $user->setLastName(strtoupper($data['lastname']));
            $user->setCompany($data['company']);
            $user->setAttribute('c', 'BE');
            $user->setAttribute('co', 'Belgium');
            $user->setAttribute('physicalDeliveryOfficeName', 'Belgium [BE]');
            $user->setAttribute('importedFrom', 'AD-ONLY');
            $user->setAccountExpiry($data['expirationDate']->getTimestamp());

            if (!empty($data['jobTitle'])) {
                $user->setTitle($data['jobTitle']);
                $user->setDescription($data['jobTitle']);
            }
            $user->setUserPrincipalName($data['login']);
            $user->setEmail($data['login']);
            // Get & clean phone info
            $mobile = ActiveDirectoryHelper::cleanUpPhoneNumber($data['phone']);
            if (!empty($mobile)) {
                $user->setMobileNumber($mobile);
            }
            $dn = new DistinguishedName();
            // Get or create the country OU
            $dn->setBase($organizationalUnit->getDn());
            $dn->addCn($user->getCommonName());
            $user->setDn($dn);

            // Save the basic data of the user
            if ($user->save() && !empty($user->getEmail())) {
                // Generate a password
                $password = ActiveDirectoryHelper::generatePassword();
                $user->setPassword($password);
                $this->accountService->updateCredentials($user, $password);
                $this->accountService->setGeneratedPassword($user->getEmail(), $password);
                $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
                if (!$user->save()) {
                    return new ActiveDirectoryResponse(
                        "User '" . $data['login'] . "' unable to enable and set '" . $password . "' as default password",
                        ActiveDirectoryResponseStatus::EXCEPTION,
                        ActiveDirectoryResponseType::CREATE,
                        ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                    );
                }

                $this->bisDir->synchronize($user, $password);

                return new ActiveDirectoryResponse(
                    "User '" . $data['login'] . "' created with password '" . $password . "'",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::CREATE,
                    ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                );
            }

            return new ActiveDirectoryResponse(
                "Unable to create this user '" . $data['login'] . "'",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataAdUser($user)
            );
        }
        return new ActiveDirectoryResponse(
            "This user '" . $data['login'] . "' already exist",
            ActiveDirectoryResponseStatus::FAILED,
            ActiveDirectoryResponseType::CREATE,
            $data
        );
    }

    /**
     * Create a user from SuccessFactor API in Active directory
     *
     * @return ActiveDirectoryResponse
     */
    public function createFromSfApi(array $data)
    {
        // Check user exist in AD
        $adAccount = $this->getUser($data['emailEnabel']);

        // Create user in AD
        if (null === $adAccount) {
            $bisCountryRepository = $this->bis->getRepository(BisCountry::class);
            $bisCountry = $bisCountryRepository->findOneBy(['couIsocode3letters'=>$data['countryWorkplace']]);
            // Init a new Active Directory user
            $user = $this->activeDirectory->connect()->make()->user();
            // Get the correct organizational unit
            $organizationalUnit = $this->checkOuExistByName($bisCountry->getCouIsocode3letters());

            // Set user data in Active Directory format
            $user->setEmployeeId($data['id']);
            $user->setCommonName(ucfirst($data['firstname']) . ' ' . strtoupper($data['lastname']));
            $user->setAccountName(strtolower(substr($data['firstname'], 0, 1) . substr($data['lastname'], 0, 7)) . $data['id']);
            $user->setDisplayName(ucfirst($data['firstname']) . ', ' . strtoupper($data['lastname']));
            $user->setInitials($data['gender']);
            $user->setEmployeeType($data['jobClass']);
            $user->setAttribute('language', strtolower($data['preferredLanguage']));
            $user->setProxyAddresses(['SMTP:' . $data['emailEnabel']]);
            $user->setFirstName(ucfirst($data['firstname']));
            $user->setLastName(strtoupper($data['lastname']));
            $user->setAttribute('c', $bisCountry->getCouIsocode2letters());
            $user->setAttribute('co', $bisCountry->getCouName());
            $user->setAttribute('physicalDeliveryOfficeName', $bisCountry->getCouName() . ' [' . $bisCountry->getCouIsocode2letters() . ']');
            $user->setAttribute('importedFrom', 'AD-ONLY');
            $user->setInfo(
                json_encode([
                    'warning' => 'Early created by password, remove attribute importedFrom after startDate',
                    'startDate' => ($data['startDate'] === null) ? null : $data['startDate']->format('Y-m-d'),
                    'endDate' => ($data['endDate'] === null) ? null : $data['endDate']->format('Y-m-d')
                ])
            );

            if (!empty($data['jobTitle'])) {
                $user->setTitle($data['jobTitle']);
                $user->setDescription($data['jobTitle']);
            }
            $user->setUserPrincipalName($data['emailEnabel']);
            $user->setEmail($data['emailEnabel']);
            // Get & clean phone info
            $mobile = ActiveDirectoryHelper::cleanUpPhoneNumber($data['mobile']);
            $phone = ActiveDirectoryHelper::cleanUpPhoneNumber($data['phone']);
            if (!empty($mobile)) {
                $user->setMobileNumber($mobile);
            }
            if (!empty($phone)) {
                $user->setTelephoneNumber($phone);
            }
            $dn = new DistinguishedName();
            // Get or create the country OU
            $dn->setBase($organizationalUnit->getDn());
            $dn->addCn($user->getCommonName());
            $user->setDn($dn);

            // Save the basic data of the user
            if ($user->save() && !empty($user->getEmail())) {
                // Generate a password
                $password = ActiveDirectoryHelper::generatePassword();
                $user->setPassword($password);
                $this->accountService->updateCredentials($user, $password);
                $this->accountService->setGeneratedPassword($user->getEmail(), $password);
                $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
                if (!$user->save()) {
                    return new ActiveDirectoryResponse(
                        "User '" . $data['emailEnabel'] . "' unable to enable and set '" . $password . "' as default password",
                        ActiveDirectoryResponseStatus::EXCEPTION,
                        ActiveDirectoryResponseType::CREATE,
                        ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                    );
                }

                return new ActiveDirectoryResponse(
                    "User '" . $data['emailEnabel'] . "' created with password '" . $password . "'",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::CREATE,
                    ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                );
            }

            return new ActiveDirectoryResponse(
                "Unable to create this user '" . $data['emailEnabel'] . "'",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataAdUser($user)
            );
        }
        return new ActiveDirectoryResponse(
            "This user '" . $data['emailEnabel'] . "' already exist",
            ActiveDirectoryResponseStatus::FAILED,
            ActiveDirectoryResponseType::CREATE,
            $data
        );
    }

    /**
     * Sync language from GO4HR to AD
     *
     * @param User          $adAccount The AD User
     * @param BisPersonView $bisPerson The BIS User [GO4HR]
     *
     * @return ActiveDirectoryResponse
     */
    public function syncLang(User $adAccount, BisPersonView $bisPerson)
    {
        $diffData = [];

        // Get new language
        $lang = strtolower($bisPerson->getPreferredLanguage());
        // Get current language
        $oldLang = $adAccount->getFirstAttribute('preferredLanguage');

        // Update language if necessary
        if (!empty($lang)) {
            if ($lang !== $oldLang) {
                $diffData['preferredLanguage'] = [
                    'attribute' => 'preferredLanguage',
                    'value' => $lang,
                    'original' => $oldLang,
                ];
                $adAccount->setAttribute('preferredLanguage', $lang);
            }
        }

        // Save all these modification
        if (!empty($diffData)) {
            if ($adAccount->save()) {
                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' successfully updated in Active Directory",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::UPDATE,
                    ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' can't be disabled in Active Directory",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::UPDATE,
                ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $adAccount->getEmail() . "' already up to date in Active Directory",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::UPDATE,
            ActiveDirectoryHelper::getDataAdUser(
                $adAccount,
                [
                    'preferredLanguage' => $lang,
                ]
            )
        );
    }

    /**
     * Sync phone number & mobile from GO4HR to AD
     *
     * @param User          $adAccount The AD User
     * @param BisPersonView $bisPerson The BIS User [GO4HR]
     *
     * @return ActiveDirectoryResponse
     */
    public function syncPhone(User $adAccount, BisPersonView $bisPerson)
    {
        $diffData = [];

        // Remove useless (0)
        $telephone = ActiveDirectoryHelper::cleanUpPhoneNumber($bisPerson->getTelephone());
        $mobile = ActiveDirectoryHelper::cleanUpPhoneNumber($bisPerson->getMobile());

        // Get current phone number & mobile
        $oldTelephone = $adAccount->getTelephoneNumber();
        $oldMobile = $adAccount->getFirstAttribute('mobile');

        // Update phone number if necessary
        if (!empty($telephone)) {
            if ($telephone !== $oldTelephone) {
                $diffData['TelephoneNumber'] = [
                    'attribute' => 'TelephoneNumber',
                    'value' => $telephone,
                    'original' => $oldTelephone,
                ];
                $adAccount->setTelephoneNumber($telephone);
            }
        }
        // Update mobile if necessary
        if (!empty($mobile)) {
            if ($mobile !== $oldMobile) {
                $diffData['mobile'] = [
                    'attribute' => 'mobile',
                    'value' => $mobile,
                    'original' => $oldMobile,
                ];
                $adAccount->setFirstAttribute('mobile', $mobile);
            }
        }

        // Save all these modification
        if (!empty($diffData)) {
            if ($adAccount->save()) {
                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' successfully updated in Active Directory",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::UPDATE,
                    ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' can't be disabled in Active Directory",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::UPDATE,
                ActiveDirectoryHelper::getDataAdUser($adAccount, ['diff' => $diffData])
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $adAccount->getEmail() . "' already up to date in Active Directory",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::UPDATE,
            ActiveDirectoryHelper::getDataAdUser(
                $adAccount,
                [
                    'telephone' => $telephone,
                    'mobile' => $mobile,
                ]
            )
        );
    }

    /**
     * Update email for a account
     *
     * @param User   $adAccount The user account
     * @param String $newEmail  The new email address
     * @param bool   $keepProxy Keep current proxy as secondary
     *
     * @return ActiveDirectoryResponse
     */
    public function changeEmail(User $adAccount, String $newEmail, $keepProxy = false)
    {
        // Get current email
        $email = $adAccount->getEmail();

        if ($email !== $newEmail) {
            // Get current proxyAddresses
            $proxyAddresses = $adAccount->getProxyAddresses();

            // Create proxyAddress data
            if (true === $keepProxy) {
                foreach ($proxyAddresses as $key => $proxyAddress) {
                    if (strpos($proxyAddress, 'SMTP:')) {
                        $proxyAddresses[$key] = str_replace("SMTP:", 'smtp:', $proxyAddress);
                    }
                }
            } else {
                $proxyAddresses = [
                    'SMTP:' . $newEmail,
                ];
            }

            $adAccount->setEmail($newEmail)
                ->setUserPrincipalName($newEmail)
                ->setProxyAddresses($proxyAddresses);

            if (!$adAccount->save()) {
                return new ActiveDirectoryResponse(
                    "Unable to set new email ['" . $newEmail . "']  for this user '" . $adAccount->getEmail() . "'",
                    ActiveDirectoryResponseStatus::FAILED,
                    ActiveDirectoryResponseType::UPDATE
                );
            }

            return new ActiveDirectoryResponse(
                "Email ['" . $newEmail . "'] for user '" . $adAccount->getEmail() . "' successfully updated in Active Directory",
                ActiveDirectoryResponseStatus::DONE,
                ActiveDirectoryResponseType::UPDATE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $adAccount->getEmail() . "' already up to date in Active Directory",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::UPDATE
        );
    }

    /**
     * @param string $email
     * @param string $newEmail
     * @param bool   $keepProxy
     *
     * @return null|User
     */
    public function findAndChangeEmail(string $email, string $newEmail, $keepProxy)
    {
        $adUser = $this->getUser($email);

        if (null !== $adUser) {
            // Check proxy
            $proxyAddresses = [];
            if ($keepProxy) {
                $proxyAddresses = str_replace('SMTP:', 'smtp:', $adUser->getProxyAddresses());
                $key = array_search('smtp:' . $newEmail, $proxyAddresses);
                if (false !== $key) {
                    unset($proxyAddresses[$key]);
                }
            }
            // Add new email to proxy as primary email O365
            if (false === in_array('SMTP:' . $newEmail, $proxyAddresses)) {
                $proxyAddresses[] = 'SMTP:' . $newEmail;
            }

            $adUser->setEmail($newEmail)
                ->setUserPrincipalName($newEmail)
                ->setProxyAddresses($proxyAddresses);

            $adUser->save();
        }

        return $adUser;
    }

    /**
     * @param string $email
     *
     * @return null|User
     */
    public function fixSip(string $email)
    {
        $adUser = $this->getUser($email);

        if (null !== $adUser) {
            $adUser->setAttribute('msrtcSip-PrimaryUserAddress', $email);

            $adUser->save();
        }

        return $adUser;
    }

    public function createEmptyUser()
    {
        return $this->activeDirectory->connect()->make()->user();
    }

    /**
     * Disable a specific account
     *
     * @param User $adAccount The user account
     *
     * @return ActiveDirectoryResponse
     */
    private function disableAccount(User $adAccount): ActiveDirectoryResponse
    {
        $ouName = 'DISABLED-USER';

        if (true === $adAccount->isActive()) {
            $adAccount->setUserAccountControl(AccountControl::ACCOUNTDISABLE);
            $adAccount->setAccountExpiry(time());
            if ($adAccount->save()) {
                // Check Organizational Unit exist
                $organizationalUnit = $this->checkOuExistByName($ouName);

                if (false === $organizationalUnit) {
                    return new ActiveDirectoryResponse(
                        "Organizational unit '" . $ouName . "' doesn't exist!",
                        ActiveDirectoryResponseStatus::EXCEPTION,
                        ActiveDirectoryResponseType::DISABLE,
                        ActiveDirectoryHelper::getDataAdUser(
                            $adAccount,
                            [
                                'from' => $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName()),
                                'to' => $ouName,
                            ]
                        )
                    );
                }
                //$rdn = 'CN=' . $adAccount->getCommonName();
                $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
                if (!$adAccount->move($organizationalUnit->getDn())) {
                    $from = implode('/', array_reverse($oldOu->getComponents('ou')));
                    $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->getComponents('ou')));

                    return new ActiveDirectoryResponse(
                        "User '" . $adAccount->getEmail() . "'disabled but can't be moved from '" . $from . "' to '" . $to . "'",
                        ActiveDirectoryResponseStatus::FAILED,
                        ActiveDirectoryResponseType::DISABLE,
                        ActiveDirectoryHelper::getDataAdUser(
                            $adAccount,
                            [
                                'from' => $oldOu->get(),
                                'to' => $organizationalUnit->getDn(),
                            ]
                        )
                    );
                }
                $from = implode('/', array_reverse($oldOu->getComponents('ou')));
                $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->getComponents('ou')));

                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' disabled and moved from '" . $from . "' to '" . $to . "'",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::DISABLE,
                    ActiveDirectoryHelper::getDataAdUser(
                        $adAccount,
                        [
                            'from' => $oldOu->get(),
                            'to' => $organizationalUnit->getDn(),
                        ]
                    )
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' can't be disabled in Active Directory",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::DISABLE,
                ActiveDirectoryHelper::getDataAdUser($adAccount)
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $adAccount->getEmail() . "' already disabled in Active Directory",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::DISABLE,
            ActiveDirectoryHelper::getDataAdUser($adAccount)
        );
    }

    /**
     *
     * @param BisPersonView $bisUser
     *
     * @return ActiveDirectoryResponse
     *
     * @throws AdldapException
     */
    private function createUser(BisPersonView $bisUser): ActiveDirectoryResponse
    {
        // Init a new Active Directory user
        $user = $this->activeDirectory->connect()->make()->user();

        // Check if a language preference exist for this user
        $userLanguage = $this->em->getRepository(UserLanguage::class)->findOneBy(['userId'=>$bisUser->getEmployeeId()]);
        if (null !== $userLanguage) {
            $bisUser->setLanguage($userLanguage->getShortLanguage());
        }

        // Get the correct organizational unit
        $organizationalUnit = $this->checkOuExistByName($bisUser->getCountry());

        if (false === $organizationalUnit) {
            return new ActiveDirectoryResponse(
                "Organizational unit for country '" . $bisUser->getCountry() . "' doesn't exist!",
                ActiveDirectoryResponseStatus::EXCEPTION,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        }
        if (empty($bisUser->getEmail())) {
            return new ActiveDirectoryResponse(
                "Unable to create this user '" . $bisUser->getFullName() . "' no email!",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        }
        // Set BIS data in Active Directory format
        $user = ActiveDirectoryHelper::bisPersonToAdUser($bisUser, $user, $organizationalUnit);

        // Save the basic data of the user
        if ($user->save() && !empty($user->getEmail())) {
            // Generate a password
            $password = ActiveDirectoryHelper::generatePassword();
            $user->setPassword($password);
            $this->accountService->updateCredentials($user, $password);
            $this->accountService->setGeneratedPassword($user->getEmail(), $password);
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);

            if ($user->getCountry() !== 'BE') {
                $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT | AccountControl::DONT_EXPIRE_PASSWORD);
            }

            if (!$user->save()) {
                return new ActiveDirectoryResponse(
                    "User '" . $bisUser->getEmail() . "' unable to enable and set '" . $password . "' as default password",
                    ActiveDirectoryResponseStatus::EXCEPTION,
                    ActiveDirectoryResponseType::CREATE,
                    ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                );
            }

            $this->bisDir->synchronize($user, $password);

            return new ActiveDirectoryResponse(
                "User '" . $bisUser->getEmail() . "' created with password '" . $password . "'",
                ActiveDirectoryResponseStatus::DONE,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
            );
        }

        return new ActiveDirectoryResponse(
            "Unable to create this user '" . $bisUser->getEmail() . "'",
            ActiveDirectoryResponseStatus::FAILED,
            ActiveDirectoryResponseType::CREATE,
            ActiveDirectoryHelper::getDataBisUser($bisUser)
        );
    }

    /**
     * Enable a specific account
     *
     * @param User $adAccount The user account
     *
     * @return ActiveDirectoryResponse
     */
    private function enableExistingAccount(User $adAccount): ActiveDirectoryResponse
    {
        if (!$adAccount->isActive() && $adAccount->isExpired()) {
            if ($adAccount->getCountry() !== 'BE') {
                $adAccount->setUserAccountControl(AccountControl::NORMAL_ACCOUNT | AccountControl::DONT_EXPIRE_PASSWORD);
            } else {
                $adAccount->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            }
            $adAccount->setAccountExpiry(null);
            if ($adAccount->save()) {
                $this->bisDir->synchronize($adAccount);

                return new ActiveDirectoryResponse(
                    "User '" . $adAccount->getEmail() . "' successfully enabled in Active Directory",
                    ActiveDirectoryResponseStatus::DONE,
                    ActiveDirectoryResponseType::UPDATE
                );
            }

            return new ActiveDirectoryResponse(
                "User '" . $adAccount->getEmail() . "' can't be enabled in Active Directory",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::UPDATE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $adAccount->getEmail() . "' already enabled in Active Directory",
            ActiveDirectoryResponseStatus::NOTHING_TO_DO,
            ActiveDirectoryResponseType::UPDATE
        );
    }

    public function forceSync(BisPersonView $bisPersonView)
    {
        // Check user exist in AD
        $adAccount = $this->getUser($bisPersonView->getEmail());

        // Create user in AD
        if (null === $adAccount) {
            $log = $this->createUser($bisPersonView);
            $adAccount = $this->getUser($bisPersonView->getEmail());
        } else {
            // Set BIS data in Active Directory format
            [$adAccount, $diffData] = ActiveDirectoryHelper::bisPersonUpdateAdUser($bisPersonView, $adAccount);
            if (!empty($diffData)) {
                if ($adAccount->save()) {
                    $moved = $this->userNeedToMove($bisPersonView, $adAccount);
                    if (ActiveDirectoryResponseStatus::ACTION_NEEDED === $moved->getStatus()) {
                        // Move user in AD
                        $this->moveUser($bisPersonView, $adAccount);
                    }
                    $this->bisDir->synchronize($adAccount);
                }
            }
        }

        return $adAccount;
    }
}
