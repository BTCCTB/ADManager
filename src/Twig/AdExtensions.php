<?php

namespace App\Twig;

use AuthBundle\Service\ActiveDirectory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class AdExtensions
 *
 * @package App\Twig
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class AdExtensions extends AbstractExtension
{
    /**
     * @var ActiveDirectory
     */
    private $ad;

    public function __construct(ActiveDirectory $ad)
    {
        $this->ad = $ad;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('adTimestamp', [$this, 'adTimestampToUnix']),
            new TwigFilter('userCountry', [$this, 'getUserCountry']),
        ];
    }

    public function adTimestampToUnix($win32Time)
    {
        $winSecs = (int) ($win32Time / 10000000); // divide by 10 000 000 to get seconds
        $unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
        return $unixTimestamp;
    }

    public function getUserCountry($email)
    {
        $user = $this->ad->getUser($email);

        if ($user != null) {
            return $user->getCountry();
        }

        return null;
    }
}
