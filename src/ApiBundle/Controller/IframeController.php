<?php

namespace Api\Controller;

use BisBundle\Service\BisPersonView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IframeController
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class IframeController extends AbstractController
{
    /**
     * @Route("/starters/{key}", methods={"GET"}, name="api_iframe_starters")
     * @param BisPersonView $bisPersonView
     *
     * @return Response
     */
    public function starters(BisPersonView $bisPersonView, string $key = null)
    {
        if (empty($key)) {
            return new Response("Forbidden (no api key!)", Response::HTTP_FORBIDDEN);
        } elseif ($key === $_ENV["INTRANET_API_KEY"]) {
            return $this->render(
                'Iframe/starters.html.twig',
                [
                    'starters' => $bisPersonView->getStarters(),
                ]
            );
        } else {
            return new Response("Unauthorized (invalid api key!)", Response::HTTP_FORBIDDEN);
        }
    }
}
