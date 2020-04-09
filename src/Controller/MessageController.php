<?php

namespace App\Controller;

use App\Entity\MessageLog;
use App\Form\MessageFormType;
use App\Service\SmsGatewayMe;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessageController
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @Route("/message", name="message_")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render(
            'Message/index.html.twig',
            [
                'controller_name' => 'MessageController',
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     * @IsGranted("ROLE_SMS")
     * @param SmsGatewayMe         $smsService The service to send SMS
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(SmsGatewayMe $smsService, Request $request)
    {
        //TODO: API Call SMS
        //TODO: Store SMSLog
        $form = $this->createForm(MessageFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var MessageLog $messageLog
             */
            $messageLog = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($messageLog);
            $entityManager->flush();

            $this->addFlash('Success', 'Your message has been processed and will be sent as soon as possible.');
            $this->redirectToRoute('homepage');
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'oh it doesn\'t look good ! Check the errors below.');
        }

        return $this->render(
            'Message/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
