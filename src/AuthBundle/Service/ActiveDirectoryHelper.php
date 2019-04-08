<?php

namespace AuthBundle\Service;

use Adldap\Models\Attributes\DistinguishedName;
use Adldap\Models\OrganizationalUnit;
use Adldap\Models\User;
use BisBundle\Entity\BisPersonView;

/**
 * Class ActiveDirectoryHelper
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class ActiveDirectoryHelper
{
    /**
     * Convert a BIS Person to a user of Active Directory
     *
     * @param BisPersonView      $bisPersonView BIS person data
     * @param User               $user          AD user data
     *
     * @param OrganizationalUnit $unit The user OrganizationalUnit
     *
     * @return User The AD user account.
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function bisPersonToAdUser(BisPersonView $bisPersonView, User $user, OrganizationalUnit $unit): User
    {
        $user->setEmployeeId($bisPersonView->getEmployeeId());
        $user->setCommonName($bisPersonView->getCommonName());
        $user->setAccountName($bisPersonView->getAccountName());
        $user->setDisplayName($bisPersonView->getDisplayName());
        $user->setFirstName($bisPersonView->getFirstname());
        $user->setLastName($bisPersonView->getLastname());
        if (!empty($bisPersonView->getInitials())) {
            $user->setInitials($bisPersonView->getInitials());
        }
//        if (!empty($bisPersonView->getBusinessCategory())) {
        //            $user->setAttribute('businessCategory', $bisPersonView->getBusinessCategory());
        //        }
        $user->setAttribute('businessRoles', $bisPersonView->getBusinessRoles());
        $user->setCompany($bisPersonView->getCompany());
        $user->setDepartment($bisPersonView->getDepartment());
//        $user->setCountry($bisPersonView->getCountry());
        $user->setAttribute('c', $bisPersonView->getAttribute('c'));
        $user->setAttribute('co', $bisPersonView->getAttribute('co'));
        if (!empty($bisPersonView->getInfo())) {
            $user->setInfo($bisPersonView->getInfo());
        }
        if (!empty($bisPersonView->getTitle())) {
            $user->setTitle($bisPersonView->getTitle());
        }
        if (!empty($bisPersonView->getDescription())) {
            $user->setDescription($bisPersonView->getDescription());
        }
        $user->setUserPrincipalName($bisPersonView->getUserPrincipalName());
        $user->setEmail($bisPersonView->getEmail());
        $user->setProxyAddresses($bisPersonView->getProxyAddresses());

        // Get & clean phone info
        $telephone = self::cleanUpPhoneNumber($bisPersonView->getTelephone());
        $mobile = self::cleanUpPhoneNumber($bisPersonView->getMobile());
        if (!empty($telephone)) {
            $user->setTelephoneNumber($telephone);
        }
        if (!empty($mobile)) {
            $user->setFirstAttribute('mobile', $mobile);
        }
        // For HQ only set HomeDrive & login script
        if ($user->getCountry() === 'BE') {
            $user->setHomeDirectory(getenv('HOME_BASE_DIRECTORY') . $user->getAccountName());
            $user->setHomeDrive(getenv('HOME_DRIVE'));
            $user->setScriptPath("login.bat");
        }

        $dn = new DistinguishedName();
        // Get or create the country OU
        $dn->setBase($unit->getDn());
        $dn->addCn($user->getCommonName());
        $user->setDn($dn);

        return $user;
    }

    /**
     * @param string $password
     *
     * @return bool|string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function checkPasswordComplexity(string $password)
    {
        if (\strlen($password) < 8) {
            return 'Error E101 - Your password is too short. Your password must be at least 8 characters long.';
        }
        if (!preg_match('/[\d]/', $password)) {
            return 'Error E102 - Your password must contain at least one number.';
        }
        if (!preg_match('/[a-zA-Z]/', $password)) {
            return 'Error E103 - Your password must contain at least one letter.';
        }

        $complexity = 0;
        if (preg_match('/[A-Z]/', $password)) {
            $complexity++;
        }
        if (preg_match('/[a-z]/', $password)) {
            $complexity++;
        }
        if (preg_match('/[\d]/', $password)) {
            $complexity++;
        }
        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $complexity++;
        }

        if ($complexity < 3) {
            return 'Error E104 - Your password does not respect the rules of complexity.';
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
    public static function generatePassword(): string
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

    public static function createCountryDistinguishedName(String $countryIso3): string
    {
        $organizationalUnit = new DistinguishedName();
        $organizationalUnit->setBase('dc=ad4dev,dc=local');
        if ($countryIso3 === 'BEL') {
            $organizationalUnit->addOu('Users');
            $organizationalUnit->addOu('Enabel-Belgium');
        } else {
            $organizationalUnit->addOu($countryIso3);
            $organizationalUnit->addOu('Enabel-World');
        }

        return $organizationalUnit->get();
    }

    public static function getDataBisUser(BisPersonView $bisPersonView, array $extraData = []): array
    {
        $info = $bisPersonView->getInfo();
        if (!empty($info)) {
            if (\is_array($info) && !empty($info[0])) {
                $info = $info[0];
            }
            $info = json_decode($info);
        }
        if (!property_exists($info, 'endDate')) {
            $info->endDate = '';
        }
        if (!property_exists($info, 'startDate')) {
            $info->startDate = '';
        }

        return array_merge(
            [
                'EmployeeId' => $bisPersonView->getEmployeeId(),
                'CommonName' => $bisPersonView->getCommonName(),
                'AccountName' => $bisPersonView->getAccountName(),
                'DisplayName' => $bisPersonView->getDisplayName(),
                'FirstName' => $bisPersonView->getFirstname(),
                'LastName' => $bisPersonView->getLastname(),
                'Initials' => $bisPersonView->getInitials(),
                'businessRoles' => $bisPersonView->getBusinessRoles(),
                'Department' => $bisPersonView->getDepartment(),
                'Company' => $bisPersonView->getCompany(),
                'Country' => $bisPersonView->getCountry(),
                'Title' => $bisPersonView->getTitle(),
                'Description' => $bisPersonView->getDescription(),
                'UserPrincipalName' => $bisPersonView->getUserPrincipalName(),
                'Email' => $bisPersonView->getEmail(),
                'ProxyAddresses' => $bisPersonView->getProxyAddresses(),
                'OrganizationalUnit' => $bisPersonView->getOrganizationalUnit(),
                'StartDate' => $info->startDate,
                'EndDate' => $info->endDate,
            ],
            $extraData
        );
    }

    public static function getDataAdUser(User $adUser, array $extraData = []): array
    {
        $info = $adUser->getInfo();
        if (!empty($info)) {
            if (\is_array($info) && !empty($info[0])) {
                $info = $info[0];
            }
            $info = json_decode($info);
        } else {
            $info = new \ArrayObject();
        }
        if (!property_exists($info, 'endDate')) {
            $info->endDate = '';
        }
        if (!property_exists($info, 'startDate')) {
            $info->startDate = '';
        }

        return array_merge(
            [
                'EmployeeId' => $adUser->getEmployeeId(),
                'CommonName' => $adUser->getCommonName(),
                'AccountName' => $adUser->getAccountName(),
                'DisplayName' => $adUser->getDisplayName(),
                'FirstName' => $adUser->getFirstName(),
                'LastName' => $adUser->getLastName(),
                'Initials' => $adUser->getInitials(),
                'businessRoles' => $adUser->getAttribute('businessRoles'),
                'Department' => $adUser->getDepartment(),
                'Company' => 'Enabel',
                'Country' => $adUser->getCountry(),
                'Title' => $adUser->getTitle(),
                'Description' => $adUser->getDescription(),
                'UserPrincipalName' => $adUser->getUserPrincipalName(),
                'Email' => $adUser->getEmail(),
                'ProxyAddresses' => $adUser->getProxyAddresses(),
                'OrganizationalUnit' => $adUser->getDnBuilder()->removeCn($adUser->getCommonName()),
                'StartDate' => $info->startDate,
                'EndDate' => $info->endDate,
            ],
            $extraData
        );
    }

    /**
     * Convert a BIS Person data to update a user of Active Directory
     *
     * @param BisPersonView      $bisPersonView BIS person data
     * @param User               $user          AD user data
     *
     * @return array The AD user account and data diff
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function bisPersonUpdateAdUser(BisPersonView $bisPersonView, User $user)
    {
        $diffData = [];

        if ($bisPersonView->getEmployeeId() != $user->getEmployeeId()) {
            $diffData['EmployeeId'] = [
                'attribute' => 'EmployeeId',
                'value' => $bisPersonView->getEmployeeId(),
                'original' => $user->getEmployeeId(),
            ];
            $user->setEmployeeId($bisPersonView->getEmployeeId());
        }

        if ($bisPersonView->getDisplayName() !== $user->getDisplayName()) {
            $diffData['DisplayName'] = [
                'attribute' => 'DisplayName',
                'value' => $bisPersonView->getDisplayName(),
                'original' => $user->getDisplayName(),
            ];
            $user->setDisplayName($bisPersonView->getDisplayName());
        }
        if ($bisPersonView->getFirstname() !== $user->getFirstName()) {
            $diffData['firstName'] = [
                'attribute' => 'firstName',
                'value' => $bisPersonView->getFirstname(),
                'original' => $user->getFirstName(),
            ];
            $user->setFirstName($bisPersonView->getFirstname());
        }
        if ($bisPersonView->getLastname() !== $user->getLastName()) {
            $diffData['lastName'] = [
                'attribute' => 'lastName',
                'value' => $bisPersonView->getLastname(),
                'original' => $user->getLastName(),
            ];
            $user->setLastName($bisPersonView->getLastname());
        }
        if ($bisPersonView->getInitials() !== $user->getInitials()) {
            $diffData['initials'] = [
                'attribute' => 'initials',
                'value' => $bisPersonView->getInitials(),
                'original' => $user->getInitials(),
            ];
            $user->setInitials($bisPersonView->getInitials());
        }
//        if ($bisPersonView->getBusinessCategory() !== $user->getFirstAttribute('businessCategory')) {
        //            $user->setAttribute('businessCategory', $bisPersonView->getBusinessCategory());
        //        }
        if ($bisPersonView->getBusinessRoles() !== $user->getFirstAttribute('businessRoles')) {
            $user->setAttribute('businessRoles', $bisPersonView->getBusinessRoles());
        }
        if ($bisPersonView->getDepartment() !== $user->getDepartment()) {
            $diffData['department'] = [
                'attribute' => 'department',
                'value' => $bisPersonView->getDepartment(),
                'original' => $user->getDepartment(),
            ];
            $user->setDepartment($bisPersonView->getDepartment());
        }
        if ($bisPersonView->getCompany() !== $user->getCompany()) {
            $diffData['company'] = [
                'attribute' => 'company',
                'value' => $bisPersonView->getCompany(),
                'original' => $user->getCompany(),
            ];
            $user->setCompany($bisPersonView->getCompany());
        }
        if ($bisPersonView->getAttribute('c') !== $user->getFirstAttribute('c')) {
            $diffData['c'] = [
                'attribute' => 'c',
                'value' => $bisPersonView->getAttribute('c'),
                'original' => $user->getFirstAttribute('c'),
            ];
            $user->setAttribute('c', $bisPersonView->getAttribute('c'));
        }
        if ($bisPersonView->getAttribute('co') !== $user->getFirstAttribute('co')) {
            $diffData['co'] = [
                'attribute' => 'co',
                'value' => $bisPersonView->getAttribute('co'),
                'original' => $user->getFirstAttribute('co'),
            ];
            $user->setAttribute('co', $bisPersonView->getAttribute('co'));
        }

        if ($bisPersonView->getInfo() !== $user->getInfo()) {
            $diffData['info'] = [
                'attribute' => 'info',
                'value' => $bisPersonView->getInfo(),
                'original' => $user->getInfo(),
            ];
            $user->setInfo($bisPersonView->getInfo());
        }

        if ($bisPersonView->getDescription() !== $user->getDescription()) {
            $diffData['description'] = [
                'attribute' => 'description',
                'value' => $bisPersonView->getDescription(),
                'original' => $user->getDescription(),
            ];
            $user->setDescription($bisPersonView->getDescription());
        }

        if ($bisPersonView->getTitle() !== $user->getTitle()) {
            $diffData['title'] = [
                'attribute' => 'title',
                'value' => $bisPersonView->getTitle(),
                'original' => $user->getTitle(),
            ];
            $user->setTitle($bisPersonView->getTitle());
        }

        // Email
        if ($bisPersonView->getEmail() !== $user->getEmail()) {
            $diffData['email'] = [
                'attribute' => 'email',
                'value' => $bisPersonView->getEmail(),
                'original' => $user->getEmail(),
            ];
            $user->setEmail($bisPersonView->getEmail());
        }

        $proxyAddresses = $user->getProxyAddresses();
        $bisProxyAddresses = $bisPersonView->getProxyAddresses();
        foreach ($bisProxyAddresses as $bisProxyAddress) {
            if (!\in_array($bisProxyAddress, $proxyAddresses, false)) {
                $proxyAddresses[] = $bisProxyAddress;
            }
        }
        if ($proxyAddresses !== $user->getProxyAddresses()) {
            $diffData['proxyAddresses'] = [
                'attribute' => 'proxyAddresses',
                'value' => $proxyAddresses,
                'original' => $user->getProxyAddresses(),
            ];
            $user->setProxyAddresses($proxyAddresses);
        }

        // Clean phone number
        $telephone = self::cleanUpPhoneNumber($bisPersonView->getTelephone());
        // Update phone number if necessary
        if ($telephone !== $user->getTelephoneNumber()) {
            $diffData['TelephoneNumber'] = [
                'attribute' => 'TelephoneNumber',
                'value' => $telephone,
                'original' => $user->getTelephoneNumber(),
            ];
            $user->setTelephoneNumber($telephone);
        }

        // Clean mobile number
        $mobile = self::cleanUpPhoneNumber($bisPersonView->getMobile());
        // Update mobile if necessary
        if ($mobile !== $user->getFirstAttribute('mobile')) {
            $diffData['mobile'] = [
                'attribute' => 'mobile',
                'value' => $mobile,
                'original' => $user->getFirstAttribute('mobile'),
            ];
            $user->setFirstAttribute('mobile', $mobile);
        }

        // Update home drive & login script for HQ users
        if ($user->getCountry() === 'BE') {
            // Set home directory only if empty not update !!!
            if (empty($user->getHomeDirectory())) {
                $homedirectory = getenv('HOME_BASE_DIRECTORY') . $user->getAccountName();
                $homedrive = getenv('HOME_DRIVE');
                $diffData['homedrive'] = [
                    'attribute' => 'homedrive',
                    'value' => $homedrive,
                    'original' => $user->getHomeDrive(),
                ];
                $diffData['homedirectory'] = [
                    'attribute' => 'homedirectory',
                    'value' => $homedirectory,
                    'original' => $user->getHomeDirectory(),
                ];
                $user->setHomeDirectory($homedirectory);
                $user->setHomeDrive($homedrive);
            }
            // Set home directory only if empty not update !!!
            if (empty($user->getScriptPath())) {
                $diffData['scriptpath'] = [
                    'attribute' => 'scriptpath',
                    'value' => 'login.bat',
                    'original' => $user->getScriptPath(),
                ];
                $user->setScriptPath('login.bat');
            }
        } else {
            // empty home drive & login script
            if (!empty($user->getHomeDrive())) {
                $diffData['homedrive'] = [
                    'attribute' => 'homedrive',
                    'value' => null,
                    'original' => $user->getHomeDrive(),
                ];
                $user->setHomeDrive(null);
            }
            if (!empty($user->getHomeDirectory())) {
                $diffData['homedirectory'] = [
                    'attribute' => 'homedirectory',
                    'value' => null,
                    'original' => $user->getHomeDirectory(),
                ];
                $user->setHomeDirectory(null);
            }
            if (!empty($user->getScriptPath())) {
                $diffData['scriptpath'] = [
                    'attribute' => 'scriptpath',
                    'value' => null,
                    'original' => $user->getScriptPath(),
                ];
                $user->setScriptPath(null);
            }
        }

        return [$user, $diffData];
    }

/**
 * Get the current date in desired format
 *
 * @param string $format Date format [optional][default: Y-m-d]
 *
 * @return string The current date
 */
    public static function today($format = 'Y-m-d'): string
    {
        $date = new \DateTime();

        return $date->format($format);
    }

    /**
     * @param String $phoneNumber
     *
     * @return mixed|String
     */
    public static function cleanUpPhoneNumber(? String $phoneNumber)
    {
        if (empty($phoneNumber)) {
            return null;
        }
        // Remove useless characters like '(0)', '.', '-', '/', ' '
        $phoneNumber = str_replace(array('(0)', '.', '-', '/', ' '), '', $phoneNumber);

        return $phoneNumber;
    }

    public static function compareWithBis(User $bisData, User $adData)
    {
        // Attributes list
        $attributes = [
            'employeeId',
            'cn',
            'displayname',
            'samaccountname',
            'givenname',
            'sn',
            'initials',
            'businessRoles',
            'company',
            'department',
            'c',
            'co',
            'info',
            'title',
            'description',
            'userprincipalname',
            'mail',
            'proxyaddresses',
            'telephonenumber',
            'mobile',
            'homedirectory',
            'homedrive',
            'scriptpath',
            'Distinguishedname',
        ];

        // Compare data
        $diff = [];
        foreach ($attributes as $attribute) {
            $diff[$attribute] = [
                'ad' => json_encode($adData->getAttribute($attribute)),
                'bis' => json_encode($bisData->getAttribute($attribute)),
            ];
        }

        return $diff;
    }
}
