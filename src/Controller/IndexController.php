<?php

namespace App\Controller;

use App\Service\QrCodeUser;
use Auth\Service\ActiveDirectory;
use Bis\Service\BisPersonView;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function myAccount(
        BisPersonView $bisPersonView,
        QrCodeUser $qrCodeUser,
        TranslatorInterface $translator
    ): Response {
        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());
        $now = new \DateTime('now');
        $passwordLastSet = null;
        if ($user->getPasswordLastSetTimestamp() !== null) {
            $passwordLastSet = (new \DateTime())->setTimestamp($user->getPasswordLastSetTimestamp());
        }
        $passwordAges = ($passwordLastSet !== null) ? $passwordLastSet->diff($now)->format('%a') : null;
        $dateForceChange = (new \DateTime(''))->setDate(2021, 02, 15);
        $needToChange = $dateForceChange->diff($passwordLastSet)->invert;
        if ($needToChange === 1) {
            $this->addFlash('danger', $translator->trans('alert.account.force.change.password'));
            return $this->redirectToRoute('account_change_password');
        }
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
