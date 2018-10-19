<?php

namespace App\Controller;

use App\Entity\IncidentSeverity;
use App\Form\IncidentSeverityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/incident/severity", name="incident_severity_")
 */
class IncidentSeverityController extends Controller
{
    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index()
    {
        $incidentSeverities = $this->getDoctrine()
            ->getRepository(IncidentSeverity::class)
            ->findAll();

        return $this->render('incident_severity/index.html.twig', ['incidentSeverities' => $incidentSeverities]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    function new (Request $request) {
        $incidentSeverity = new IncidentSeverity();
        $form = $this->createForm(IncidentSeverityType::class, $incidentSeverity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($incidentSeverity);
            $em->flush();

            return $this->redirectToRoute('incident_severity_edit', ['id' => $incidentSeverity->getId()]);
        }

        return $this->render('incident_severity/new.html.twig', [
            'incidentSeverity' => $incidentSeverity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(IncidentSeverity $incidentSeverity)
    {
        return $this->render('incident_severity/show.html.twig', [
            'incidentSeverity' => $incidentSeverity,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, IncidentSeverity $incidentSeverity)
    {
        $form = $this->createForm(IncidentSeverityType::class, $incidentSeverity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('incident_severity_edit', ['id' => $incidentSeverity->getId()]);
        }

        return $this->render('incident_severity/edit.html.twig', [
            'incidentSeverity' => $incidentSeverity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, IncidentSeverity $incidentSeverity)
    {
        if (!$this->isCsrfTokenValid('delete' . $incidentSeverity->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('incident_severity_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($incidentSeverity);
        $em->flush();

        return $this->redirectToRoute('incident_severity_index');
    }
}
