<?php

namespace Api\Controller;

use BisBundle\Service\BisPersonView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class StaffController
 *
 * @package App\ApiBundle\Controller
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @Route("/staff", name="api_staff_")
 */
class StaffController extends AbstractController
{
    /**
     * @Route("/starters/{format}/{limit}", name="starters", methods={"GET"}, requirements={"limit"="\d+", "format"="json|csv"})
     */
    public function starters(SerializerInterface $serializer, BisPersonView $bisPersonView, $format = "json", $limit = 15)
    {
        $starters = $bisPersonView->getStarters($limit);
        $data = $serializer->serialize($starters, $format, ['groups' => 'starters']);
        $response = new Response($data);
        if ($format == "csv") {
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename=starters.csv');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }

    /**
     * @Route("/finishers/{format}/{limit}", name="finishers", methods={"GET"}, requirements={"limit"="\d+", "format"="json|csv"})
     */
    public function finishers(SerializerInterface $serializer, BisPersonView $bisPersonView, $format = "json", $limit = 15)
    {
        $finishers = $bisPersonView->getFinishers($limit);
        $data = $serializer->serialize($finishers, $format, ['groups' => 'starters']);
        $response = new Response($data);
        if ($format == "csv") {
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename=starters.csv');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
