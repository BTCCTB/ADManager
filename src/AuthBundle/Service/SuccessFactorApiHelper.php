<?php

namespace AuthBundle\Service;

/**
 * Class SuccessFactorApiHelper.
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class SuccessFactorApiHelper
{
    /**
     * Convert a SuccessFactor Date to DateTime object.
     */
    public static function SFDateToDateTime( ? string $sfDate) :  ? \DateTime
    {
        if (!empty($sfDate)) {
            $date = new \DateTime('now');
            preg_match('/\/Date\((.*)\)\//', $sfDate, $milliseconds);
            if (isset($milliseconds[1])) {
                $date->setTimestamp($milliseconds[1] / 1000);
                if ($date->format('Y-m-d') === '9999-12-31') {
                    return null;
                }
                return $date;
            }
        }

        return null;
    }

    /**
     * Convert a DateTime to SuccessFactor Date.
     */
    public static function dateTimeToSFDate( ? \DateTime $date) :  ? string
    {
        if (null != $date) {
            return '/Date(' . ($date->getTimestamp() * 1000) . ')/';
        }

        return null;
    }

    public static function cleanPhoneNumber( ? string $phone)
    {
        if (null != $phone) {
            return '+' . str_replace('(0)', '', str_replace(' ', '', $phone));
        }

        return null;
    }

    public static function jobTypeFromJobCode( ? string $jobCode,  ? string $jobType)
    {
        $type = $jobType;
        if (empty($jobType)) {
            if (!empty($jobCode)) {
                $type = substr($jobCode, 0, 2);
            }
        }
        switch ($type) {
            case 'LO' : return 'Local';
            case 'HQ' : return 'HQ';
            case 'EX' : return 'Expat';
        }
        return null;
    }

    public static function positionFromCode(string $positionCode) :  ? int
    {
        return (int) filter_var($positionCode, FILTER_SANITIZE_NUMBER_INT);
    }

}
