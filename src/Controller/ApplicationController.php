<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApplicationController
 *
 * @package Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @IsGranted("ROLE_USER")
 */
class ApplicationController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Route("/my-apps", name="application-my-apps")
     * @param ApplicationRepository $applicationRepository
     *
     * @return Response
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function myApps(ApplicationRepository $applicationRepository): Response
    {
        $applications = $applicationRepository->findAllApplications();

        $ad = $this->get('auth.active_directory');
        $user = $ad->checkUserExistByUsername($this->getUser()->getUsername());

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
            ]
        );
    }

    /**
     * @Route("/admin/application", name="application_index")
     * @param ApplicationRepository $applicationRepository
     *
     * @return Response
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function index(ApplicationRepository $applicationRepository): Response
    {
        $applications = $applicationRepository->findAllApplications();

        return $this->render('Application/index.html.twig', ['applications' => $applications]);
    }

}
