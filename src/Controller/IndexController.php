<?php

namespace App\Controller;

use App\Service\QrCodeUser;
use AuthBundle\Service\ActiveDirectory;
use BisBundle\Service\BisPersonView;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 *
 * @package Controller
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @IsGranted("ROLE_USER")
 * @see \App\Tests\Controller\IndexControllerTest
 */
class IndexController extends AbstractController
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    public function __construct(ActiveDirectory $activeDirectory)
    {
        $this->activeDirectory = $activeDirectory;
    }

    /**
     * @Route("/", name="homepage")
     * @Route("/account/my", name="my_account")
     *
     * @param BisPersonView      $bisPersonView
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function myAccount(BisPersonView $bisPersonView, QrCodeUser $qrCodeUser): Response
    {
        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());
        $now = new \DateTime('now');
        $passwordLastSet = new \DateTime();
        $passwordLastSet->setTimestamp($user->getPasswordLastSetTimestamp());
        $passwordAges = $passwordLastSet->diff($now)->format('%a');
        $qrCodeData = $qrCodeUser->generateBase64($user);

        return $this->render(
            'Index/homepage.html.twig',
            [
                'passwordAges' => $passwordAges,
                'user' => $user,
                'country' => $user->getFirstAttribute('c'),
                'language' => strtoupper(substr($user->getFirstAttribute('preferredLanguage'), 0, 2)),
                'starters' => $bisPersonView->getStarters(),
                'finishers' => $bisPersonView->getFinishers(),
                'qrcode' => $qrCodeData,
            ]
        );
    }
}
