<?php

namespace AuthBundle\Service;

use Adldap\Adldap;
use Adldap\Connections\Provider;
use Adldap\Models\User;

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

    public function __construct(
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
        return $this->activeDirectory->search()->findBy('mail', $email);
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

    public function checkUserExist($email): bool
    {
        $user = $this->activeDirectory->search()->findBy('mail', $email);
        if ($user instanceof User) {
            return true;
        }

        return false;
    }

    /**
     * Get all users from Active Directory
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function getAllUsers()
    {
        return $this->activeDirectory->search()->all();
    }

    public function updateUserFromBis(string $email)
    {
        // TODO: To be continue ...
        //Get user from BIS with email address
        // SELECT * FROM bis_person_view WHERE per_email like '$email';
        $bisUser = new \stdClass();

        // Get existing user from AD
        $user = $this->activeDirectory->search()->findBy('cn', str_replace('@btcctb.org', '', $bisUser->per_email));
        if (!empty($user)) {
            // Update User
            // Set the user profile details.
            $user->setCommonName(str_replace('@btcctb.org', '', $bisUser->per_email));
            // 1+7 login
            $user->setAccountName(str_replace('@btcctb.org', '', $bisUser->per_email));
            $user->setDisplayName($bisUser->per_firstname . ', ' . strtoupper($bisUser->per_lastname));
            $user->setFirstName($bisUser->per_firstname);
            $user->setLastName($bisUser->per_lastname);
            $user->setInitials($bisUser->per_sex);
            $user->setTitle($bisUser->per_function);
            $user->setDescription($bisUser->per_function);
            // nom+prenom
            //$user->setUserPrincipalName(str_replace('@btcctb.org', '@enabel.be', $bis_user->per_email));
            $user->setInfo($bisUser->per_language);
            //$user->setTelephoneNumber($bis_user->per_telephone);

            //$user->setDepartment($bis_user->per_country_workplace);
            $user->setCompany('Enabel');

            // Save the user.
            if ($user->save()) {
                echo "Update user";
            }
        }
    }

    public function createUserFromBIS(string $email)
    {
        // TODO: To be continue ...

        //Get user from BIS with email address
        // SELECT * FROM bis_person_view WHERE per_email like '$email';
        $bisUser = new \stdClass();

        // Check user doen't exist in AD
        $user = $this->activeDirectory->search()->findBy('cn', str_replace('@btcctb.org', '', $bisUser->per_email));
        if (empty($user)) {
            // Construct a new user instance.
            $user = $this->activeDirectory->make()->user();

            // Set the user profile details.
            $user->setCommonName(str_replace('@btcctb.org', '', $bisUser->per_email));
            // 1+7 login
            $user->setAccountName(str_replace('@btcctb.org', '', $bisUser->per_email));
            $user->setDisplayName($bisUser->per_firstname . ', ' . strtoupper($bisUser->per_lastname));
            $user->setFirstName($bisUser->per_firstname);
            $user->setLastName($bisUser->per_lastname);
            $user->setInitials($bisUser->per_sex);
            $user->setTitle($bisUser->per_function);
            $user->setDescription($bisUser->per_function);
            // nom+prenom
            //$user->setUserPrincipalName(str_replace('@btcctb.org', '@enabel.be', $bis_user->per_email));
            $user->setUserPrincipalName($bisUser->per_email);
            $user->setEmail($bisUser->per_email);
            $user->setCompany('Enabel');

            $dn = $user->getDnBuilder();

            $dn->addCn($user->getCommonName());
            // Country workplace
            $dn->addOu('BDI');
            // Default Ou for field
            $dn->addOu('Enabel-World');

            $user->setDn($dn);

            // Save the new user.
            if ($user->save()) {
                echo "user $bisUser->per_email has been created<br/>";
                // Set new user password
                $user->setPassword('Difficult+');
                // Set account active
                $user->setUserAccountControl(512);

                // Save the user.
                if ($user->save()) {
                    // The password was saved successfully.
                    echo "$bisUser->per_email has been activated<br/><br/>";
                    return true;
                }
            }
        }
    }

}
