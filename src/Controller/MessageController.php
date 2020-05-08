<?php

namespace App\Controller;

use App\Entity\MessageLog;
use App\Form\MessageFormType;
use App\Message\SmsMessage;
use App\Repository\MessageLogRepository;
use App\Service\EnabelGroupSms;
use App\Service\SmsGatewayMe;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MessageController
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 * @Route("/message", name="message_")
 * @IsGranted("ROLE_SMS")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/", name="log")
     */
    public function log(MessageLogRepository $messageLogRepository)
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $messages = $messageLogRepository->findAll();
        } else {
            $messages = $messageLogRepository->findBy([
                'sender' => $user,
            ]);
        }

        return $this->render(
            'Message/log.html.twig',
            [
                'messages' => $messages,
            ]
        );
    }

    /**
     * @Route("/create", name="create")
     * @param SmsGatewayMe         $smsService The service to send SMS
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, EnabelGroupSms $enabelGroupSms)
    {
        //TODO: API Call SMS
        $form = $this->createForm(MessageFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var MessageLog $messageLog
             */
            $messageLog = $form->getData();
            $recipients = $messageLog->getRecipient();
            foreach ($recipients as $recipient) {
                if ($messageLog->getMultilanguage()) {
                    $contacts = $enabelGroupSms->getRecipientByLanguages($recipient);
                    $phoneNumbers['EN'] = $contacts['EN']->map(function ($contact) {
                        return $contact->getPhone();
                    });
                    $phoneNumbers['FR'] = $contacts['FR']->map(function ($contact) {
                        return $contact->getPhone();
                    });
                    $phoneNumbers['NL'] = $contacts['NL']->map(function ($contact) {
                        return $contact->getPhone();
                    });

                    if (!empty($phoneNumbers['EN']) && !empty($messageLog->getMessage())) {
                        foreach ($phoneNumbers['EN'] as $phoneNumber) {
                            $this->dispatchMessage(new SmsMessage($messageLog->getMessage(), $phoneNumber));
                        }
                    }
                    if (!empty($phoneNumbers['FR']) && !empty($messageLog->getMessageFr())) {
                        foreach ($phoneNumbers['FR'] as $phoneNumber) {
                            $this->dispatchMessage(new SmsMessage($messageLog->getMessageFr(), $phoneNumber));
                        }
                    }
                    if (!empty($phoneNumbers['NL']) && !empty($messageLog->getMessageNl())) {
                        foreach ($phoneNumbers['NL'] as $phoneNumber) {
                            $this->dispatchMessage(new SmsMessage($messageLog->getMessageNl(), $phoneNumber));
                        }
                    }
                } else {
                    $contacts = $enabelGroupSms->getRecipients($recipient);
                    $phoneNumbers = $contacts->map(function ($contact) {
                        return $contact->getPhone();
                    });
                    foreach ($phoneNumbers as $phoneNumber) {
                        $this->dispatchMessage(new SmsMessage($messageLog->getMessage(), $phoneNumber));
                    }
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($messageLog);
            $entityManager->flush();

            $this->addFlash('success', 'Your message has been processed and will be sent as soon as possible.');
            return $this->redirectToRoute('homepage');
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

    /**
     * @Route("/detail/{id}", name="detail", requirements={"id":"\d+"})
     *
     * @ParamConverter("id", class="App:MessageLog")
     *
     * @param MessageLog $messageLog
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detail(MessageLog $messageLog): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(MessageFormType::class, $messageLog);

        return $this->render(
            'Message/create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
