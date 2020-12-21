<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twilio\TwiML\MessagingResponse;

class ErrorController extends AbstractController
{
    /**
     * @Route("/error/autoreply", name="twilio_autoreply")
     * @Template()
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function twilioAutoReply(TranslatorInterface $translator)
    {
        $response = new MessagingResponse();
        $response->message(
            $translator->trans('This is an unmonitored number and your text has been deleted from our system. ') .
                $translator->trans('If you require support, please use https://support.enabel.be .') .
                $translator->trans('Thank you, Enabel IT Team.')
        );

        return new Response($response->asXML(), Response::HTTP_OK, ['Content-Type'=>'text/xml']);
    }
}
