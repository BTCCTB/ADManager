<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\AdldapException;
use Adldap\Connections\Provider;
use Adldap\Models\Attributes\AccountControl;
use Adldap\Models\Attributes\DistinguishedName;
use Adldap\Models\OrganizationalUnit;
use Adldap\Models\User;
use Adldap\Models\UserPasswordPolicyException;
use App\Service\Account;
use BisBundle\Entity\BisPersonView;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class ActiveDirectory
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class ActiveDirectory
{
    /**
     * @var Provider
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

    public function __construct(
        EntityManager $em,
        string $hosts,
        string $baseDn,
        string $adminUsername,
        string $adminPassword,
        string $accountSuffix = '',
        int $port = 636,
        bool $followReferrals = false,
        bool $useTls = true,
        bool $useSsl = true,
        Account $accountService
    ) {

        $adldap = new Adldap();
        $adldap->addProvider([
            'domain_controllers' => explode(',', $hosts),
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

        $this->activeDirectory = $adldap->connect();
        $this->em = $em;
        $this->baseDn = $baseDn;
        $this->accountService = $accountService;
    }

    /**
     * Find a active directory user by email
     *
     * @param string $email His email
     *
     * @return User The user.
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
     * Check credentials for a user by email
     *
     * @param string $email    His email
     * @param string $password His password
     *
     * @return bool Returns true if the credentials are valid.
     */
    public function checkCredentials(string $email, string $password): bool
    {
        $user = $this->getUser($email);
        return $this->activeDirectory->auth()->attempt($user->getUserPrincipalName(), $password);
    }

    /**
     * Change user password
     *
     * @param string $email    His email
     * @param string $password His password
     *
     * @return bool Returns true if the password is updated.
     * @throws \Adldap\AdldapException
     */
    public function changePassword(string $email, string $password): bool
    {
        $user = $this->getUser($email);

        if (ActiveDirectoryHelper::checkPasswordComplexity($password) === true) {
            $user->setPassword($password);
            $this->accountService->updateCredentials($user, $password);
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

        $user = $this->activeDirectory->search()->findBy('userprincipalname', $username);

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

        $user = $this->activeDirectory->search()->findBy('mail', $email);
        $userEnabel = $this->activeDirectory->search()->findBy('mail', str_replace('@btcctb.org', '@enabel.be', $email));
        $userBtc = $this->activeDirectory->search()->findBy('mail', str_replace('@enabel.be', '@btcctb.org', $email));
        if ($user instanceof User) {
            return $user;
        } elseif ($userEnabel instanceof User) {
            return $userEnabel;
        } elseif ($userBtc instanceof User) {
            return $userBtc;
        }

        return false;
    }

    /**
     * Check if user exist in AD
     *
     * @param integer $employeeID The employee ID [BIS ID]
     *
     * @return bool|mixed
     */
    public function checkUserExistByEmployeeID($employeeID)
    {

        $user = $this->activeDirectory->search()->findBy('employeeid', $employeeID);

        if ($user instanceof User) {
            return $user;
        }

        return false;
    }

    /**
     * Get all users from Active Directory
     *
     * @param string $sortField The specified field to sort
     * @param string $sortDirection The specified direction to sort
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
     * @param string $sortField The specified field to sort
     * @param string $sortDirection The specified direction to sort
     * @return User[]|Collection<User>
     */
    public function getFieldUsers($sortField = 'cn', $sortDirection = 'ASC')
    {
        $fieldDn = new DistinguishedName();
        $fieldDn->addDc('ad4dev')->addDc('local');
        $fieldDn->addOu('Enabel-World');

        return $this->activeDirectory
            ->search()
            ->users()
            ->whereEquals('objectClass', 'user')
            ->in($fieldDn->get())
            ->sortBy($sortField, $sortDirection)
            ->paginate('10000')
            ->getResults();
    }

    /**
     * Get all hq users from Active Directory
     *
     * @param string $sortField The specified field to sort
     * @param string $sortDirection The specified direction to sort
     * @return User[]|Collection<User>
     */
    public function getHqUsers($sortField = 'cn', $sortDirection = 'ASC')
    {
        $hqDn = new DistinguishedName();
        $hqDn->addDc('ad4dev')->addDc('local');
        $hqDn->addOu('Users')->addOu('Enabel-Brussels');

        return $this->activeDirectory
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
            $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

    public function getCountryStatUsers()
    {
        $stats = [];
        $stats['HQ'] = count($this->getHqUsers());
        $stats['Field'] = count($this->getFieldUsers());

        return $stats;
    }

    /**
     * Set default password & define account as normal
     *
     * @param String      $username The username [firstname.lastname@company.domain]
     *
     * @param String|null $password The new password
     *
     * @return bool
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws UserPasswordPolicyException
     * TODO: Change return type to ActiveDirectoryResponse
     */
    public function resetAccount(String $username, String $password): bool
    {
        $user = $this->checkUserExistByUsername($username);
        if ($user !== false) {
            // Set account active
            $user->setUserAccountControl(512);
            if ($password !== null) {
                if (ActiveDirectoryHelper::checkPasswordComplexity($password) === true) {
                    $user->setPassword($password);
                    $this->accountService->updateCredentials($user, $password);
                    return $user->save();
                } else {
                    throw new UserPasswordPolicyException('The password does not conform to the password policy!');
                }
            }
        }
        throw new UsernameNotFoundException('The username does not correspond to a valid user!');
    }

    /**
     * Check if OU exist in AD
     *
     * @param String $name The name of the OU
     * @param bool   $autoCreate Create the OU if not exist
     *
     * @return OrganizationalUnit|false
     */
    public function checkOuExistByName($name, $autoCreate = false)
    {
        if ($name === 'BEL') {
            $name = 'Users';
        }

        $organizationalUnit = $this->activeDirectory->search()->ous()->findBy('name', $name);

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
     * @param OrganizationalUnit $ou The organizational unit
     *
     * @return bool
     */
    public function isUserMemberOf(User $user, OrganizationalUnit $ou): bool
    {
        /**
         * @var User[] $members
         */
        $members = $this->activeDirectory->search()->users()->setDn($ou->getDn())->paginate(10000)->getResults();
        foreach ($members as $member) {
            if ($user->getDn() === $member->getDn()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @return \Adldap\Models\OrganizationalUnit|bool
     */
    public function createCountryOu($name)
    {
        $ou = $this->activeDirectory->make()->ou();
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
     * Update DisplayName in AD with GO4HR data
     * TODO: Change return type to ActiveDirectoryResponse
     */
    public function fixDisplayName()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Error Log
        $logs = [];

        // Get BisPersonView Repository
        $bisPersonView = $this->em->getRepository('BisBundle:BisPersonView');

        $users = $this->getAllUsers('userprincipalname');
        foreach ($users as $user) {
            $email = $user->getEmail();
            $state = '<comment>OK</comment>';
            $bisUser = null;
            if (!empty($email)) {
                $bisUser = $bisPersonView->getUserByEmail($email);
            }
            if (!empty($bisUser)) {
                if (strcmp($user->getDisplayName(), $bisUser->getDisplayName()) !== 0) {
                    $user->setDisplayName($bisUser->getDisplayName());
                    $user->setFirstName($bisUser->getFirstname());
                    $user->setLastName($bisUser->getLastname());
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $user->getDisplayName(),
                        'new' => $bisUser->getDisplayName(),
                        'state' => $state,
                    ];
                }
            } else {
                if (strtolower($user->getLastName()) === 'desk') {
                    $newDn = strtoupper($user->getLastName()) . ', ' . ucfirst(strtolower($user->getFirstName()));
                    $user->setDisplayName($newDn);
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $user->getDisplayName(),
                        'new' => $newDn,
                        'state' => $state,

                    ];
                } elseif (strtolower($user->getFirstName()) === 'desk') {
                    $newDn = strtoupper($user->getFirstName()) . ', ' . ucfirst(strtolower($user->getLastName()));
                    $user->setDisplayName($newDn);
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $user->getDisplayName(),
                        'new' => $newDn,
                        'state' => $state,

                    ];
                } elseif (!empty($user->getLastName()) && !empty($user->getFirstName())) {
                    $newDn = strtoupper($user->getLastName()) . ', ' . ucfirst(strtolower($user->getFirstName()));
                    $user->setDisplayName($newDn);
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $user->getDisplayName(),
                        'new' => $newDn,
                        'state' => $state,

                    ];
                }
            }
        }

        return $logs;
    }

    /**
     * @return array
     * TODO: Change return type to ActiveDirectoryResponse
     */
    public function fixProxyAddresses()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Error Log
        $logs = [];

        /**
         * @var User[] $users
         */
        $users = $this->getAllUsers('userprincipalname');
        foreach ($users as $user) {
            $email = strtolower($user->getEmail());
            $state = '<comment>OK</comment>';
            if (!empty($email)) {
                $proxyAddresses = $user->getProxyAddresses();
                $emailPart = explode('@', $email);
                if (!\in_array('SMTP:' . $emailPart[0] . '@enabel.be', $proxyAddresses, false)) {
                    $proxyAddresses[] = 'SMTP:' . $emailPart[0] . '@enabel.be';
                }
                if (!\in_array('smtp:' . $emailPart[0] . '@btcctb.org', $proxyAddresses, false)) {
                    $proxyAddresses[] = 'smtp:' . $emailPart[0] . '@btcctb.org';
                }
                $user->setProxyAddresses($proxyAddresses);
                if (!$user->save()) {
                    $state = '<error>FAILED</error>';
                }
                $logs[] = [
                    'user' => $user->getEmail(),
                    'current' => json_encode($user->getProxyAddresses()),
                    'new' => json_encode($proxyAddresses),
                    'state' => $state,

                ];
            }
        }

        return $logs;
    }

    /**
     * @return array
     * TODO: Change return type to ActiveDirectoryResponse
     */
    public function fixAttributes()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Error Log
        $logs = [];

        /**
         * @var BisPersonView[] $users
         */
        $users = $this->em->getRepository('BisBundle:BisPersonView')->findAllFieldUser();
        foreach ($users as $user) {
            $adAccount = $this->checkUserExistByEmail($user->getEmail());
            if ($adAccount !== false) {
                $sex = $user->getSex();
                $adAccount->setInitials($sex);
                $adAccount->setAttribute('businessCategory', str_replace('@enabel.be', '@btcctb.org', $adAccount->getEmail()));
                if (!$adAccount->save()) {
                    $logs[] = [
                        'user' => $adAccount->getUserPrincipalName(),
                        'state' => '<error>FAILED</error>',
                    ];
                }
            } else {
                $logs[] = [
                    'user' => $user->getEmail(),
                    'state' => '<error>NOT FOUND</error>',
                ];
            }
        }

        return $logs;
    }

    /**
     * @return array
     * TODO: Change return type to ActiveDirectoryResponse
     */
    public function fixUserPrincipalName()
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Error Log
        $logs = [];

        /**
         * @var User[] $users
         */
        $users = $this->getAllUsers('userprincipalname');
        foreach ($users as $user) {
            $email = strtolower($user->getEmail());
            $state = '<comment>OK</comment>';
            if (!empty($email)) {
                $emailPart = explode('@', $email);
                if ($user->getUserPrincipalName() !== $emailPart[0] . '@enabel.be') {
                    $user->setUserPrincipalName($emailPart[0] . '@enabel.be');
                    if (!$user->save()) {
                        $state = '<error>FAILED</error>';
                    }
                    $logs[] = [
                        'user' => $user->getUserPrincipalName(),
                        'current' => $user->getUserPrincipalName(),
                        'new' => $emailPart[0] . '@enabel.be',
                        'state' => $state,
                    ];
                }
            }
        }

        return $logs;
    }

    /**
     * @param null $country
     *
     * @return ActiveDirectoryResponse[]
     * @throws \Adldap\AdldapException
     */
    public function cronTaskSynchronize($country = null)
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // ActiveDirectoryResponse Logs
        $logs = [];

        // Get BisPersonView Repository
        $bisPersonView = $this->em->getRepository('BisBundle:BisPersonView');

        // Get active users in GO4HR
        if (!empty($country)) {
            $bisUsers = $bisPersonView->findBy(['perCountryWorkplace' => $country]);
        } else {
            $bisUsers = $bisPersonView->findAll();
        }

        /**
         * @var BisPersonView[] $bisUsers
         */
        foreach ($bisUsers as $bisUser) {
            $adAccount = $this->checkUserExistByUsername($bisUser->getDomainAccount());

            if ($adAccount instanceof User) {
                // User is active?
                $logs[] = $this->enableExistingAccount($adAccount);
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
        foreach ($adUsers as $adUser) {
            $bisUser = null;
            if (!empty($adUser->getEmail())) {
                $bisUser = $bisPersonView->getUserByEmail($adUser->getEmail());
            }
            if (empty($bisUser)) {
                if ($adUser->getPhysicalDeliveryOfficeName() !== 'AD-ONLY') {
                    $logs[] = $this->disableAccount($adUser);
                }
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
        $bisPersonView = $this->em->getRepository('BisBundle:BisPersonView');
        $bisPersonSf = $this->em->getRepository('BisBundle:BisPersonSf');
        $bisContractSf = $this->em->getRepository('BisBundle:BisContractSf');

        $adUsers = $this->getAllUsers('email', 'ASC');
        foreach ($adUsers as $adUser) {
            $bisUser = null;
            if (!empty($adUser->getEmail())) {
                $bisUser = $bisPersonView->getUserByEmail($adUser->getEmail());
            }
            if (empty($bisUser)) {
                if ($adUser->getPhysicalDeliveryOfficeName() !== 'AD-ONLY') {
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

        if ($organizationalUnit === false) {
            return new ActiveDirectoryResponse(
                "Organizational unit for country '" . $bisUser->getCountry() . "' doesn't exist!",
                ActiveDirectoryResponseStatus::EXCEPTION,
                ActiveDirectoryResponseType::MOVE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        } elseif (!$this->isUserMemberOf($adAccount, $organizationalUnit)) {
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

        if ($organizationalUnit === false) {
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
        } else {
            $rdn = 'CN=' . $adAccount->getCommonName();
            $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
            if (!$adAccount->move($rdn, $organizationalUnit->getDn())) {
                $from = implode('/', array_reverse($oldOu->organizationUnits));
                $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
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
            } else {
                $from = implode('/', array_reverse($oldOu->organizationUnits));
                $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
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
        }
    }

    /**
     *
     * @param BisPersonView $bisUser
     *
     * @return ActiveDirectoryResponse
     * @throws \Adldap\AdldapException
     */
    private function createUser(BisPersonView $bisUser): ActiveDirectoryResponse
    {
        // Init a new Active Directory user
        $user = $this->activeDirectory->make()->user();

        // Get the correct organizational unit
        $organizationalUnit = $this->checkOuExistByName($bisUser->getCountry());

        if ($organizationalUnit === false) {
            return new ActiveDirectoryResponse(
                "Organizational unit for country '" . $bisUser->getCountry() . "' doesn't exist!",
                ActiveDirectoryResponseStatus::EXCEPTION,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        } elseif (empty($bisUser->getEmail())) {
            return new ActiveDirectoryResponse(
                "Unable to create this user '" . $bisUser->getFullName() . "' no email!",
                ActiveDirectoryResponseStatus::FAILED,
                ActiveDirectoryResponseType::CREATE,
                ActiveDirectoryHelper::getDataBisUser($bisUser)
            );
        } else {
            // Set BIS data in Active Directory format
            $user = ActiveDirectoryHelper::bisPersonToAdUser($bisUser, $user, $organizationalUnit);

            // Save the basic data of the user
            try {
                if ($user->save()) {
                    // Generate a password
                    $password = ActiveDirectoryHelper::generatePassword();
                    $user->setPassword($password);
                    $this->accountService->updateCredentials($user, $password);
                    $this->accountService->setGeneratedPassword($user->getEmail(), $password);
                    $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
                    if (!$user->save()) {
                        return new ActiveDirectoryResponse(
                            "User '" . $bisUser->getEmail() . "' unable to enable and set '" . $password . "' as default password",
                            ActiveDirectoryResponseStatus::EXCEPTION,
                            ActiveDirectoryResponseType::CREATE,
                            ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                        );
                    }
                    return new ActiveDirectoryResponse(
                        "User '" . $bisUser->getEmail() . "' created with password '" . $password . "'",
                        ActiveDirectoryResponseStatus::DONE,
                        ActiveDirectoryResponseType::CREATE,
                        ActiveDirectoryHelper::getDataAdUser($user, ['password' => $password])
                    );
                }
            } catch (ContextErrorException $e) {
                return new ActiveDirectoryResponse(
                    "Unable to create this user '" . $bisUser->getEmail() . "'",
                    ActiveDirectoryResponseStatus::FAILED,
                    ActiveDirectoryResponseType::CREATE,
                    ActiveDirectoryHelper::getDataBisUser($bisUser)
                );
            }
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
            $adAccount->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
            $adAccount->setAccountExpiry(null);
            if ($adAccount->save()) {
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

        if ($adAccount->isActive() === true) {
            $adAccount->setUserAccountControl(AccountControl::ACCOUNTDISABLE);
            $adAccount->setAccountExpiry(time());
            if ($adAccount->save()) {
                // Check Organizational Unit exist
                $organizationalUnit = $this->checkOuExistByName($ouName);

                if ($organizationalUnit === false) {
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
                } else {
                    $rdn = 'CN=' . $adAccount->getCommonName();
                    $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
                    if (!$adAccount->move($rdn, $organizationalUnit->getDn())) {
                        $from = implode('/', array_reverse($oldOu->organizationUnits));
                        $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
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
                    } else {
                        $from = implode('/', array_reverse($oldOu->organizationUnits));
                        $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
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
                }
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

    public function updateAccount(string $email): ActiveDirectoryResponse
    {
        $adAccount = $this->checkUserExistByUsername($email);
        $bisUser = $this->em->getRepository('BisBundle:BisPersonView')->getUserByEmail($email);

        if ($adAccount !== false && !empty($bisUser)) {
            // Set BIS data in Active Directory format
            list($adAccount, $diffData) = ActiveDirectoryHelper::bisPersonUpdateAdUser($bisUser, $adAccount);
            //$adAccount->setAccountName($original['samaccountname'][0]);

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
                ActiveDirectoryResponseType::UPDATE
            );
        }

        return new ActiveDirectoryResponse(
            "User '" . $email . "' not found! " . (($adAccount === null) ? '[NOT IN AD]' : '') . (($bisUser === null) ? '[NOT IN BIS]' : ''),
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
        $bisUser = $this->em->getRepository('BisBundle:BisPersonView')->getUserByEmail($email);

        if ($adAccount !== false && !empty($bisUser)) {
            if ($bisUser->getCommonName() !== $adAccount->getCommonName()) {
                // Change dn based on CommonName
                $newDn = $adAccount->getDnBuilder();
                $newDn->removeCn($adAccount->getCommonName());
                $newDn->addCn($bisUser->getCommonName());
                $oldDn = $adAccount->getDnBuilder()->get();

                $rdn = 'CN=' . $bisUser->getCommonName();
                $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
                if (!$adAccount->move($rdn, $oldOu->get())) {
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
            "User '" . $email . "' not found! " . (($adAccount === null) ? '[NOT IN AD]' : '') . (($bisUser === null) ? '[NOT IN BIS]' : ''),
            ActiveDirectoryResponseStatus::EXCEPTION,
            ActiveDirectoryResponseType::UPDATE,
            [
                'AD data' => (array) $adAccount,
                'BIS data' => (array) $bisUser,
            ]
        );
    }

/**
 * Move user in the 'Enabel-NoMail' Organizational Unit
 *
 * @param string $email
 *
 * @return ActiveDirectoryResponse
 */
    public function noMail(string $email): ActiveDirectoryResponse
    {
        $ouName = 'Enabel-NoMail';
        $adAccount = $this->checkUserExistByEmail($email);

        if ($adAccount !== false) {
            // Check Organizational Unit exist
            $organizationalUnit = $this->checkOuExistByName($ouName);

            if ($organizationalUnit === false) {
                return new ActiveDirectoryResponse(
                    "Organizational unit '" . $ouName . "' doesn't exist!",
                    ActiveDirectoryResponseStatus::EXCEPTION,
                    ActiveDirectoryResponseType::MOVE,
                    ActiveDirectoryHelper::getDataAdUser(
                        $adAccount,
                        [
                            'from' => $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName()),
                            'to' => $ouName,
                        ]
                    )
                );
            } else {
                $rdn = 'CN=' . $adAccount->getCommonName();
                $oldOu = $adAccount->getDnBuilder()->removeCn($adAccount->getCommonName());
                if (!$adAccount->move($rdn, $organizationalUnit->getDn())) {
                    $from = implode('/', array_reverse($oldOu->organizationUnits));
                    $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
                    return new ActiveDirectoryResponse(
                        "User '" . $adAccount->getEmail() . "' can't be moved from '" . $from . "' to '" . $to . "'",
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
                } else {
                    $from = implode('/', array_reverse($oldOu->organizationUnits));
                    $to = implode('/', array_reverse($organizationalUnit->getDnBuilder()->organizationUnits));
                    return new ActiveDirectoryResponse(
                        "User '" . $adAccount->getEmail() . "' moved from '" . $from . "' to '" . $to . "'",
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
            }
        }
        return new ActiveDirectoryResponse(
            "AD user account with email '" . $email . "' doesn't exist!",
            ActiveDirectoryResponseStatus::EXCEPTION,
            ActiveDirectoryResponseType::MOVE
        );
    }

    /**
     * Intitalize a account with generated password.
     * @param User $fieldUser
     *
     * @return ActiveDirectoryResponse
     * @throws AdldapException
     */
    public function initAccount(User $fieldUser)
    {
        // Generate un new password
        $password = ActiveDirectoryHelper::generatePassword();
        // Set the generated password
        $fieldUser->setPassword($password);
        $this->accountService->updateCredentials($fieldUser, $password);
        $this->accountService->setGeneratedPassword($fieldUser->getEmail(), $password);

        // Set default UserControl
        $fieldUser->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);

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
     * @throws AdldapException
     */
    public function synchronizeFromBis(BisPersonView $bisPersonView)
    {
        // Check user exist in AD
        $adAccount = $this->getUser($bisPersonView->getEmail());

        // Logs
        $logs = [];

        // Create user in AD
        if ($adAccount === null) {
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
}
