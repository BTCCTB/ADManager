<?php

namespace App\Twig;

use App\Service\EnabelGroupSms;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class SmsExtensions
 *
 * @package App\Twig
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class SmsExtensions extends AbstractExtension
{
    /**
     * @var EnabelGroupSms
     */
    private $enabelGroupSms;

    /**
     * SmsExtensions constructor.
     *
     * @param EnabelGroupSms $enabelGroupSms
     */
    public function __construct(EnabelGroupSms $enabelGroupSms)
    {
        $this->enabelGroupSms = $enabelGroupSms;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('smsGroup', [$this, 'getGroup']),
        ];
    }

    public function getGroup($groups, $separator = ' | ')
    {
        $groupNames = null;

        foreach ($groups as $group) {
            if (!empty($groupNames)) {
                $groupNames .= $separator;
            }

            $groupNames .= $this->enabelGroupSms->getName($group);
        }

        return $groupNames;
    }
}
