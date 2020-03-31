<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use App\Repository\CategoryRepository;
use AuthBundle\Service\ActiveDirectory;
use BisBundle\Service\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 *
 * @package Controller
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @IsGranted("ROLE_USER")
 */
class ApplicationController extends AbstractController
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
     * @Route("/my-apps", name="application-my-apps")
     * @Route("/account/my", name="my_account")
     *
     * @param CategoryRepository $categoryRepository
     * @param BisPersonView      $bisPersonView
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function myApps(ApplicationRepository $applicationRepository, BisPersonView $bisPersonView): Response
    {
        $applications = $applicationRepository->findAllApplications();

        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());

        $now = new \DateTime('now');
        $passwordLastSet = new \DateTime();
        $passwordLastSet->setTimestamp($user->getPasswordLastSetTimestamp());

        $passwordAges = $passwordLastSet->diff($now)->format('%a');

        return $this->render(
            'Application/myApps.html.twig',
            [
                'applications' => $applications,
                'passwordAges' => $passwordAges,
                'user' => $user,
                'country' => $user->getFirstAttribute('c'),
                'starters' => $bisPersonView->getStarters(),
                'finishers' => $bisPersonView->getFinishers(),
            ]
        );
    }

    /**
     * @Route("/category/apps", name="application-by-category")
     *
     * @param CategoryRepository $categoryRepository
     * @param BisPersonView      $bisPersonView
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function appsByCategory(CategoryRepository $categoryRepository, BisPersonView $bisPersonView): Response
    {
        $categories = $categoryRepository->findAll();

        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());

        $now = new \DateTime('now');
        $passwordLastSet = new \DateTime();
        $passwordLastSet->setTimestamp($user->getPasswordLastSetTimestamp());

        $passwordAges = $passwordLastSet->diff($now)->format('%a');

        return $this->render(
            'Application/appsByCategory.html.twig',
            [
                'categories' => $categories,
                'passwordAges' => $passwordAges,
                'user' => $user,
                'starters' => $bisPersonView->getStarters(),
                'finishers' => $bisPersonView->getFinishers(),
            ]
        );
    }

    /**
     * @Route("/admin/application", name="application_index")
     *
     * @param ApplicationRepository $applicationRepository
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function index(ApplicationRepository $applicationRepository): Response
    {
        $applications = $applicationRepository->findAllApplications();

        return $this->render('Application/index.html.twig', ['applications' => $applications]);
    }
}
