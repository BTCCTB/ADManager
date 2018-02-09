<?php

namespace App\Controller;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @param ApplicationRepository $applicationRepository
     *
     * @return Response
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function indexAction(ApplicationRepository $applicationRepository): Response
    {
        $applications = $applicationRepository->findAllApplications();

        return $this->render('Application/index.html.twig', ['applications' => $applications]);
    }
}
