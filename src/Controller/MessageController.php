<?php

namespace App\Controller;

use App\Entity\MessageLog;
use App\Form\MessageFormType;
use App\Message\SmsMessage;
use App\Repository\MessageLogRepository;
use App\Service\EnabelGroupSms;
use App\Service\SmsGatewayMe;
use BisBundle\Service\PhoneDirectory;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @IsGranted("ROLE_SMS_ADMIN")
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
     * @Route("/create/group", name="create_group")
     * @param Request        $request
     * @param EnabelGroupSms $enabelGroupSms
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createGroup(Request $request, EnabelGroupSms $enabelGroupSms)
    {
        $form = $this->createForm(MessageFormType::class, null, ['recipient_choices' => $enabelGroupSms::getGroupRecipientOptions()]);

        return $this->handleSmsRequest($form, $request, $enabelGroupSms);
    }

    /**
     * @Route("/create/person", name="create_person")
     * @param Request        $request
     * @param EnabelGroupSms $enabelGroupSms
     * @param PhoneDirectory $phoneDirectory
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createPerson(Request $request, EnabelGroupSms $enabelGroupSms, PhoneDirectory $phoneDirectory)
    {
        $form = $this->createForm(MessageFormType::class, null, ['recipient_choices' => $enabelGroupSms::getPersonRecipientOptions($phoneDirectory)]);

        return $this->handleSmsRequest($form, $request, $enabelGroupSms);
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
            'Message/details.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function sendSMS(ArrayCollection $group, string $message)
    {
        $phoneNumbers = $group->map(function ($contact) {
            return $contact->getPhone();
        });

        if (!empty($phoneNumbers)) {
            foreach ($phoneNumbers as $phoneNumber) {
                $this->dispatchMessage(new SmsMessage($message, $phoneNumber));
            }
        }
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Request                               $request
     * @param EnabelGroupSms                        $enabelGroupSms
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function handleSmsRequest(
        \Symfony\Component\Form\FormInterface $form,
        Request $request,
        EnabelGroupSms $enabelGroupSms
    ) {
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
                    $this->sendSMS($contacts['EN'], $messageLog->getMessage());
                    $this->sendSMS($contacts['FR'], $messageLog->getMessageFr());
                    $this->sendSMS($contacts['NL'], $messageLog->getMessageNl());
                } else {
                    $contacts = $enabelGroupSms->getRecipients($recipient);
                    $this->sendSMS($contacts, $messageLog->getMessage());
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
}
