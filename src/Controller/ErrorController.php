<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        $message =
            '<?xml version="1.0" encoding="UTF-8"?><Response><Message>'.
            $translator->trans('This is an unmonitored number and your text
has been deleted from our system. ').
            $translator->trans('If you require support, please use https://support.enabel.be .').
            $translator->trans('Thank you, Enabel IT Team.').
            '</Message></Response>';
        return new Response($message,200, ['Content-type'=> 'text/xml']);
    }
}
