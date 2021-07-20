<?php

namespace App\Controller;

use App\Entity\Officer;
use App\Form\OfficerType;
use App\Repository\OfficerRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/officer")
 * @IsGranted("ROLE_ADMIN")
 */
class OfficerController extends AbstractController
{
    /**
     * @Route("/", name="officer_index", methods={"GET"})
     */
    public function index(
        OfficerRepository $officerRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $criteria = $request->query->get('v', null);
        $request->query->remove('f');
        $request->query->remove('v');
        $query = $officerRepository->search($criteria);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            15 /*limit per page*/
        );

        return $this->render('Officer/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * @Route("/new", name="officer_new", methods={"GET","POST"})
     */
    public function new(Request $request, RouterInterface $router, OfficerRepository $officerRepository): Response
    {
        $officer = new Officer();
        $form = $this->createForm(OfficerType::class, $officer);
        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user = $officer->getUser();
                $user->addRole('ROLE_LOCAL_OFFICER');
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($officer);
                $entityManager->flush();

                return $this->redirectToRoute('officer_index', [], Response::HTTP_SEE_OTHER);
            }
        } catch (UniqueConstraintViolationException $e) {
            $officerId = $officerRepository->findOneBy(['user' => $officer->getUser()->getId()]);
            $link = $router->generate('officer_edit', ['id' => $officerId->getId()]);
            $this->addFlash('danger', "This officer already exist !\nYou can edit it <a href=".$link.">here</a>");
        }

        return $this->renderForm('Officer/new.html.twig', [
            'officer' => $officer,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="officer_show", methods={"GET"})
     */
    public function show(Officer $officer): Response
    {
        return $this->render('Officer/show.html.twig', [
            'officer' => $officer,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="officer_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Officer $officer): Response
    {
        $form = $this->createForm(OfficerType::class, $officer, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('officer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Officer/edit.html.twig', [
            'officer' => $officer,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="officer_delete", methods={"POST"})
     */
    public function delete(Request $request, Officer $officer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$officer->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($officer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('officer_index', [], Response::HTTP_SEE_OTHER);
    }
}
