<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class IndexController
 *
 * @package AppBundle\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @throws \LogicException
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction()
    {
        $ad = $this->get('auth.active_directory');
        $user = $ad->checkUserExistByUsername($this->getUser()->getUsername());
        return $this->render('AppBundle:Index:index.html.twig', ['user' => $user, 'country' => $user->getFirstAttribute('c')]);
    }
}
