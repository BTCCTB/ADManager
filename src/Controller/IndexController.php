<?php

namespace App\Controller;

use App\Entity\Application;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class IndexController
 *
 * @package Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class IndexController extends Controller
{
    /**
     * @Route("/my-account", name="my_account")
     * @throws \LogicException
     * @IsGranted("ROLE_USER")
     */
    public function indexAction()
    {
        $ad = $this->get('auth.active_directory');
        $user = $ad->checkUserExistByUsername($this->getUser()->getUsername());

        $em = $this->getDoctrine()->getManager();
        $applications = $em->getRepository(Application::class)->findBy(['enable' => 1]);

        return $this->render(
            'Index/index.html.twig',
            [
                'user' => $user,
                'country' => $user->getFirstAttribute('c'),
                'applications' => $applications,
            ]
        );
    }
}
