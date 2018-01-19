<?php

namespace AuthBundle\Controller;

use AuthBundle\Form\Type\LoginForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class SecurityController
 *
 * @package AuthBundle\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     * @Method({"GET", "POST"})
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render(
            'AuthBundle:Security:login.html.twig',
            array(
                'form' => $form->createView(),
                'error' => $error,
            )
        );
    }

    /**
     * @Route("/logout", name="security_logout")
     * @Method({"GET"})
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached');
    }

    /**
     * @Route("/redirect", name="security_redirect")
     * @Method({"GET"})
     */
    public function redirectAction()
    {
        if ($this->isGranted("ROLE_APP_ADMIN")) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->redirectToRoute('homepage');
    }
}
