<?php

namespace App\Controller;

use App\Entity\Incident;
use App\Form\IncidentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/incident", name="incident_")
 */
class IncidentController extends Controller
{
    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index()
    {
        $incidents = $this->getDoctrine()
            ->getRepository(Incident::class)
            ->findAll();

        return $this->render('incident/index.html.twig', ['incidents' => $incidents]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    function new (Request $request) {
        $incident = new Incident();
        $form = $this->createForm(IncidentType::class, $incident);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($incident);
            $em->flush();

            return $this->redirectToRoute('incident_edit', ['id' => $incident->getId()]);
        }

        return $this->render('incident/new.html.twig', [
            'incident' => $incident,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Incident $incident)
    {
        return $this->render('incident/show.html.twig', [
            'incident' => $incident,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Incident $incident)
    {
        $form = $this->createForm(IncidentType::class, $incident);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('incident_edit', ['id' => $incident->getId()]);
        }

        return $this->render('incident/edit.html.twig', [
            'incident' => $incident,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, Incident $incident)
    {
        if (!$this->isCsrfTokenValid('delete' . $incident->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('incident_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($incident);
        $em->flush();

        return $this->redirectToRoute('incident_index');
    }
}
