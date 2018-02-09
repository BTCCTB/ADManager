<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Application;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApplicationController
 *
 * @package AppBundle\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @IsGranted("ROLE_USER")
 */
class ApplicationController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @return Response
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    public function indexAction(): Response
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Application[] $applications
         */
        $applications = $em->getRepository('AppBundle:Application')->findBy(['enable' => 1]);

        return $this->render('Application/index.html.twig', ['applications' => $applications]);
    }
}
