<?php

namespace App\Service;

use Adldap\Models\User;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class QrCodeUser
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class QrCodeUser
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function generateBase64(User $user)
    {
        $qrCode = new QrCode();
        $qrCode->setText(
            "BEGIN:VCARD\n" .
            "VERSION:3.0\n" .
            "N:" . $user->getLastName() . ";" . $user->getFirstName() . "\n" .
            "TITLE:" . $user->getDescription() . "\n" .
            ((!empty($user->getMobileNumber()))?"TEL;TYPE==WORK:" . $user->getMobileNumber() . "\n":"") .
            "EMAIL;TYPE=INTERNET:" . $user->getEmail() . "\n" .
            "ORG:Enabel - " . $user->getPhysicalDeliveryOfficeName() . "\n" .
            "URL:https://www.enabel.be\n" .
            "END:VCARD"
        );
        $qrCode->setSize(250);
        $qrCode->setMargin(5);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
        $qrCode->setForegroundColor(['r' => 88, 'g' => 87, 'b' => 86, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setLogoPath($this->kernel->getProjectDir() . "/public/img/enabel-square.png");
        $qrCode->setLogoSize(48);

        return $qrCode->writeDataUri();
    }
}
