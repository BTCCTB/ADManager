<?php

namespace AuthBundle\Service;

use Adldap\Models\Entry;
use Adldap\Models\User;
use AuthBundle\AuthBundle;

/**
 * Class BisDirHelper
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class BisDirHelper
{
    const BASEDN = "dc=enabel,dc=be";

    public static function buildParentDn(String $countryCodeIso2 = null)
    {
        $parentDn = '';
        if (!empty($countryCodeIso2)) {
            $parentDn .= 'c=' . $countryCodeIso2 . ',';
        }
        $parentDn .= self::BASEDN;

        return $parentDn;
    }

    public static function buildDn(String $uid, String $countryCodeIso2 = null)
    {
        return 'uid=' . $uid . ',' . self::buildParentDn($countryCodeIso2);
    }

    /**
     * Convert a Active Directory account to a LDAP entry
     *
     * @param User  $adAccount The Active Directory account
     * @param Entry $entry The LDAP entry
     *
     * @return Entry The LDAP entry
     */
    public static function adAccountToLdapEntry(User $adAccount, Entry $entry): Entry
    {
        //TODO: Add old btcctb.org email in one of these attribute
        $entry->setCommonName($adAccount->getCommonName())
            ->setDisplayName($adAccount->getDisplayName())
            ->setAttribute('uid', $adAccount->getEmail())
            ->setAttribute('employeenumber', $adAccount->getEmployeeId())
            ->setAttribute('mail', $adAccount->getEmail())
            ->setAttribute('businesscategory', str_replace('@enabel.be', '@btcctb.org', $adAccount->getEmail()))
            ->setAttribute('givenname', $adAccount->getFirstName())
            ->setAttribute('sn', $adAccount->getLastName())
            ->setAttribute('objectclass', 'inetOrgPerson')
            ->setDn(self::buildDn($adAccount->getEmail(), $adAccount->getFirstAttribute('c')));

        if (!empty($adAccount->getInitials())) {
            $entry->setAttribute('initials', $adAccount->getInitials());
        }
        if (!empty($adAccount->getDescription())) {
            $entry->setAttribute('title', $adAccount->getDescription());
        }
        return $entry;
    }

    /**
     * Convert AD Account to LDAP Entry & generate info into a array
     *
     * @param User  $adAccount
     * @param array $extraData
     *
     * @return array
     */
    public static function getDataAdUser(User $adAccount, array $extraData = []): array
    {
        return array_merge(
            [
                'CommonName' => $adAccount->getCommonName(),
                'DisplayName' => $adAccount->getDisplayName(),
                'uid' => $adAccount->getEmail(),
                'employeenumber' => $adAccount->getEmployeeId(),
                'mail' => $adAccount->getEmail(),
                'businesscategory' => str_replace('@enabel.be', '@btcctb.org', $adAccount->getEmail()),
                'initials' => $adAccount->getInitials(),
                'givenname' => $adAccount->getFirstName(),
                'sn' => $adAccount->getLastName(),
                'title' => $adAccount->getDescription(),
                'objectclass' => 'inetOrgPerson',
                'dn' => self::buildDn($adAccount->getEmail(), $adAccount->getFirstAttribute('c')),
            ],
            $extraData
        );
    }

    /**
     * Generate LDAP Entry info into a array
     *
     * @param Entry $entry
     * @param array $extraData
     *
     * @return array
     */
    public static function getDataEntry(Entry $entry, array $extraData = []): array
    {
        return array_merge(
            [
                'CommonName' => $entry->getCommonName(),
                'DisplayName' => $entry->getDisplayName(),
                'uid' => $entry->getFirstAttribute('uid'),
                'employeenumber' => $entry->getFirstAttribute('employeenumber'),
                'mail' => $entry->getFirstAttribute('mail'),
                'businesscategory' => $entry->getFirstAttribute('businesscategory'),
                'initials' => $entry->getFirstAttribute('initials'),
                'givenname' => $entry->getFirstAttribute('givenname'),
                'sn' => $entry->getFirstAttribute('sn'),
                'title' => $entry->getFirstAttribute('title'),
                'objectclass' => $entry->getFirstAttribute('objectclass'),
                'dn' => $entry->getDn(),
            ],
            $extraData
        );
    }
}
{

}
