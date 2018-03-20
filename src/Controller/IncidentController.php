<?php

namespace App\Controller;

use App\Repository\IncidentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class IncidentController extends Controller
{
    /**
     * @Route("/incident", name="incident")
     */
    public function index(IncidentRepository $incidentRepository)
    {
        $incidents = $incidentRepository->findActive();
        $incidentStats = $incidentRepository->getStats();

        return $this->render('incident/index.html.twig', [
            'incidents' => $incidents,
            'incidentStats' => $incidentStats,
        ]);
    }
}
