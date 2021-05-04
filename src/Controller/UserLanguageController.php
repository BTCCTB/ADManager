<?php

namespace App\Controller;

use App\Entity\UserLanguage;
use App\Form\UserLanguageType;
use App\Repository\UserLanguageRepository;
use Bis\Service\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/language")
 * @IsGranted("ROLE_ADMIN")
 */
class UserLanguageController extends AbstractController
{
    /**
     * @Route("/", name="user_language_index", methods={"GET"})
     */
    public function index(UserLanguageRepository $userLanguageRepository): Response
    {
        return $this->render('user_language/index.html.twig', [
            'user_languages' => $userLanguageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_language_new", methods={"GET","POST"})
     */
    public function new(Request $request, BisPersonView $bisPersonView): Response
    {
        $userLanguage = new UserLanguage();
        $form = $this->createForm(
            UserLanguageType::class,
            $userLanguage
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userLanguage);
            $entityManager->flush();

            return $this->redirectToRoute('user_language_index');
        }

        return $this->render('user_language/new.html.twig', [
            'user_language' => $userLanguage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_language_show", methods={"GET"})
     */
    public function show(UserLanguage $userLanguage): Response
    {
        return $this->render('user_language/show.html.twig', [
            'user_language' => $userLanguage,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_language_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UserLanguage $userLanguage, BisPersonView $bisPersonView): Response
    {
        dump($userLanguage);
        $form = $this->createForm(
            UserLanguageType::class,
            $userLanguage
        );
        dump($form->get('userID'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userLanguage);
            $entityManager->flush();

            return $this->redirectToRoute('user_language_index');
        }

        return $this->render('user_language/edit.html.twig', [
            'user_language' => $userLanguage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_language_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UserLanguage $userLanguage): Response
    {
        if ($this->isCsrfTokenValid('delete' . $userLanguage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userLanguage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_language_index');
    }
}
