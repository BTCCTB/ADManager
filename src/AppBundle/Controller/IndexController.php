<?php

namespace AppBundle\Controller;

use BisBundle\Entity\BisCountry;
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $ad = $this->get('auth.active_directory');
        $user = $ad->checkUserExistByUsername($this->getUser()->getUsername());
        $bis = $this->get('doctrine.orm.bis_entity_manager');
        $country = $bis->getRepository(BisCountry::class)->getIso2Code($user->getDepartment());
        return $this->render('AppBundle:Index:index.html.twig', ['user' => $user, 'country' => $country]);
    }

    /**
     * @Route("/import", name="import_in_AD")
     */
    public function importAction()
    {
        $em = $this->getDoctrine()->getManager('bis');
        $users = $em->getRepository('BisBundle:BisPersonView')->findBy(['jobClass' => 'Expat']);

        return $this->render('AppBundle:Index:import.html.twig', ['users' => $users]);
    }
}
