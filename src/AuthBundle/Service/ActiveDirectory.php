<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\Connections\Provider;
use Adldap\Models\Attributes\AccountControl;
use Adldap\Models\Attributes\DistinguishedName;
use Adldap\Models\OrganizationalUnit;
use Adldap\Models\User;
use Adldap\Models\UserPasswordPolicyException;
use BisBundle\Entity\BisPersonView;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
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

    public function __construct(
        EntityManager $em,
        array $hosts,
        string $baseDn,
        string $adminUsername,
        string $adminPassword,
        string $accountSuffix = '',
        int $port = 636,
        bool $followReferrals = false,
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

        $this->activeDirectory = $adldap->connect();
        $this->em = $em;
        $this->baseDn = $baseDn;
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
        $user = $this->activeDirectory->search()->findBy('userprincipalname', $email);

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
     * Check if user exist in AD
     *
     * @param String $username The username [firstname.lastname]
     *
     * @return bool|mixed
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
     * @return bool|mixed
     */
    public function checkUserExistByEmail($email)
    {

        $user = $this->activeDirectory->search()->findBy('mail', $email);

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
     * @return array|Collection
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
     * @return array|Collection
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
            ->get();
    }

    /**
     * Get all hq users from Active Directory
     *
     * @param string $sortField The specified field to sort
     * @param string $sortDirection The specified direction to sort
     * @return array|Collection
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
            ->get();
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
        $stats['HQ'] = $this->getHqUsers()->count();
        $stats['Field'] = $this->getFieldUsers()->count();

        return $stats;
    }

    /**
     * Move a existing user into a new OU
     *
     * @param User $user  The user
     * @param String $newOU The new OU
     *
     * @return bool
     */
    public function moveUser(User $user, String $newOU): bool
    {
        $rdn = $user->getDnBuilder()->assembleCns();
        $countryOu = $this->checkOuExistByName($newOU, true);

        return $user->move($rdn, $countryOu->getDn());
    }

    /**
     * Add or update a account in Active Directory
     *
     * @param String $username The username [firstname.lastname@company.domain]
     *
     * @return bool
     */
    public function syncAdUser(String $username): bool
    {
        /**
         * @var BisPersonView $bisPersonView
         */
        $bisPersonView = $this->em->getRepository('BisBundle:BisPersonView')->getUserByUsername($username);

        if ($bisPersonView !== false && $bisPersonView->getDomainAccount() !== false) {
            // Check if user already exist in AD
            $user = $this->checkUserExistByUsername($bisPersonView->getDomainAccount());
            /**
             * @var User $user
             */
            if ($user === false) {
                $user = $this->activeDirectory->make()->user();
            } else {
                $this->updateOu($user, $bisPersonView);
            }
            // Set the user profile details.
            $user->setCommonName($bisPersonView->getUsername());

            // 1+7 login
            $login = $bisPersonView->getLogin();
            $user->setAccountName(strtolower($login));

            $user->setDisplayName($bisPersonView->getDisplayName());
            $user->setFirstName($bisPersonView->getFirstname());
            $user->setLastName($bisPersonView->getLastname());
            $user->setInitials($bisPersonView->getSex());
            $user->setDepartment($bisPersonView->getCountryWorkplace());
            $user->setCompany('Enabel');
            if (!empty($bisPersonView->getFunction())) {
                $function = substr($bisPersonView->getFunction(), 0, 60);
                $user->setTitle($function);
                $user->setDescription($function);
            }

            // nom.prenom
            $user->setUserPrincipalName($bisPersonView->getDomainAccount());

            // Email
            $user->setEmail($bisPersonView->getEmail());
            $user->setProxyAddresses([
                'SMTP:' . $bisPersonView->getUsername() . '@enabel.be',
                'smtp:' . $bisPersonView->getUsername() . '@btcctb.org',
            ]);

            $dn = new DistinguishedName();

            // Get or create the country OU
            $ou = $this->checkOuExistByName($bisPersonView->getCountryWorkplace(), true);
            if ($ou !== false) {
                $dn->setBase($ou->getDn());
            }
            $dn->addCn($user->getCommonName());

            $user->setDn($dn);

            // Save the user.
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }

    public function checkPasswordComplexity(string $password)
    {
        if (\strlen($password) < 8) {
            return 'Error E103 - Your password is too short.<br/>Your password must be at least 8 characters long.';
        }
        if (!preg_match('/[\d]/', $password)) {
            return 'Error E104 - Your password must contain at least one number.';
        }
        if (!preg_match('/[a-zA-Z]/', $password)) {
            return 'Error E105 - Your password must contain at least one letter.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Error E106 - Your password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Error E107 - Your password must contain at least one lowercase letter.';
        }

        return true;
    }

    /**
     * Generates a password of N length containing at least
     * one lower case letter, one uppercase letter, one digit, and one special character.
     * The remaining characters in the password are chosen at random from those four sets.
     * The available characters in each set are user friendly - there are no ambiguous characters
     * such as i, l, 1, o, 0, -, _, etc.
     *
     * @return string The random generated password
     */
    public function generatePassword(): string
    {
        //Configuration
        $length = 8;
        $sets = array();
        // Lowercase letter
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        // Uppercase letter
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        // Number
        $sets[] = '23456789';
        // Special chars
        $sets[] = '!@#$%&*?';

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        return trim($password);
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
     */
    public function resetAccount(String $username, String $password): bool
    {
        $user = $this->checkUserExistByUsername($username);
        if ($user !== false) {
            // Set account active
            $user->setUserAccountControl(512);
            if ($password !== null) {
                if ($this->checkPasswordComplexity($password) === true) {
                    $user->setPassword($password);
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
     * @return bool|mixed
     */
    public function checkOuExistByName($name, $autoCreate = false)
    {
        if ($name === 'BEL') {
            $name = 'Users';
        }

        $ou = $this->activeDirectory->search()->ous()->findBy('name', $name);

        if ($ou instanceof OrganizationalUnit) {
            return $ou;
        }
        if ($autoCreate) {
            $this->createCountryOu($name);
            return $this->checkOuExistByName($name);
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

    protected function updateOu(User $user, BisPersonView $bisPersonView): bool
    {
        if (!strpos($user->getDepartment(), $bisPersonView->getCountryWorkplace())) {
            //            if ($this->moveUser($user, $bisPersonView->getCountryWorkplace()) === true) {
            //                return true;
            //            }
        }

        return false;
    }
}
