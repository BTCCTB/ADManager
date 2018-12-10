<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use AuthBundle\Service\ActiveDirectory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 *
 * @package Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class IndexController extends AbstractController
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;

    public function __construct(ActiveDirectory $activeDirectory, ApplicationRepository $applicationRepository)
    {
        $this->activeDirectory = $activeDirectory;
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * @Route("/my-account", name="my_account")
     * @throws \LogicException
     * @IsGranted("ROLE_USER")
     */
    public function indexAction()
    {
        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());
        $applications = $this->applicationRepository->findBy(['enable' => 1]);

        $now = new \DateTime('now');
        $passwordLastSet = new \DateTime();
        $passwordLastSet->setTimestamp($user->getPasswordLastSetTimestamp());

        $passwordAges = $passwordLastSet->diff($now)->format('%a');

        return $this->render(
            'Index/index.html.twig',
            [
                'user' => $user,
                'country' => $user->getFirstAttribute('c'),
                'applications' => $applications,
                'passwordAges' => $passwordAges,
            ]
        );
    }
}
