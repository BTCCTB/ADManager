<?php

namespace AppBundle\Controller;

use Adldap\Models\User;
use AuthBundle\Form\ChangePasswordForm;
use AuthBundle\Service\ActiveDirectoryHelper;
use BisBundle\Entity\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountController
 *
 * @package AppBundle\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @Route("/account")
 * @Security("is_granted('ROLE_USER')")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="account_ad_list")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $ad = $this->get('auth.active_directory');
        /*
         * @var \Adldap\Models\User[] $accounts
         */
        $accounts = $ad->getAllUsers('Email', 'DESC')->sort();

        $countryStats = $ad->getCountryStatUsers();
        return $this->render('AppBundle:Account:index.html.twig', ['accounts' => $accounts, 'country_distribution' => $countryStats]);
    }

    /**
     * @Route("/change-password", name="account_change_password")
     * @Method({"GET", "POST"})
     * @throws \LogicException
     * @throws \Adldap\AdldapException
     */
    public function changeAction(Request $request)
    {
        $ad = $this->get('auth.active_directory');
        $bisdir = $this->get('auth.bis_dir');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(ChangePasswordForm::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if ($ad->checkCredentials($user->getEmail(), $data['current_password'])) {
                $passwordCheck = ActiveDirectoryHelper::checkPasswordComplexity($data['password']);
                if ($passwordCheck === true) {
                    if ($ad->changePassword($user->getEmail(), $data['password'])) {
                        if ($bisdir->syncPassword($user->getEmail(), $data['password'])) {
                            $this->addFlash('success', 'Password successfully changed !');
                            $this->redirectToRoute('homepage');
                        }
                    } else {
                        $this->addFlash('danger', 'Password cannot be changed !');
                    }
                } else {
                    $this->addFlash('danger', 'The new password don\'t respect the rules of complexity');
                    $this->addFlash('warning', $passwordCheck);
                }
            } else {
                $this->addFlash('danger', 'Current password don\'t match !');
            }
        }

        return $this->render('AppBundle:Account:change.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/bis", name="account_bis_list")
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws \LogicException
     */
    public function listBisAction()
    {
        $em = $this->getDoctrine()->getManager('bis');
        $users = $em->getRepository('BisBundle:BisPersonView')->findBy(['jobClass' => 'Expat']);

        return $this->render('AppBundle:Account:listBis.html.twig', ['users' => $users]);
    }

/**
 * @Route("/disable/{employeeID}", name="ad_disable_account")
 * @Security("is_granted('ROLE_ADMIN')")
 * @Method({"GET"})
 * @param integer $employeeID The employee ID
 *
 * @return RedirectResponse
 * @throws \LogicException
 */
    public function disableAction($employeeID): RedirectResponse
    {
        $ad = $this->get('auth.active_directory');
        /**
         * @var User $user
         */
        $user = $ad->checkUserExistByEmployeeID($employeeID);
        if ($ad->disableUser($user)) {
            //TODO: flash message OK
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] disabled!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_ad_list');
    }

/**
 * @Route("/enable/{employeeID}", name="ad_enable_account")
 * @Security("is_granted('ROLE_ADMIN')")
 * @Method({"GET"})
 * @param integer $employeeID The employee ID
 *
 * @return RedirectResponse
 * @throws \LogicException
 */
    public function enableAction($employeeID): RedirectResponse
    {
        $ad = $this->get('auth.active_directory');
        $user = $ad->checkUserExistByEmployeeID($employeeID);

        if ($ad->enableUser($user)) {
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] enabled!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_ad_list');
    }

/**
 * @Route("/import/{country_code}", name="ad_import_account")
 * @Security("is_granted('ROLE_ADMIN')")
 * @param String $country_code The country code
 * @Method({"GET"})
 * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
 * @throws \LogicException
 * @throws \Adldap\Models\UserPasswordPolicyException
 */
    public function importAction($country_code)
    {
        $em = $this->getDoctrine()->getManager('bis');
        $ad = $this->get('auth.active_directory');
        $users = [];

        if (!empty($country_code)) {
            // Get Users from BIS
            $users = $em->getRepository('BisBundle:BisPersonView')->findBy([
                'perCountryWorkplace' => $country_code,
            ]);
        }

        $importedUsers = [];

        foreach ($users as $user) {
            /**
             * @var BisPersonView $user
             */
            // Add/Update user in AD
            $reset = $sync = $exist = false;
            $password = '';
            if ($user->getDomainAccount() !== false) {
                $exist = $ad->checkUserExistByUsername($user->getDomainAccount());
                $sync = $ad->syncAdUser($user->getUsername());
                if (!$exist) {
                    $password = $ad->generatePassword();
                    $reset = $ad->resetAccount($user->getDomainAccount(), $password);
                }
            }
            $importedUsers[] = [
                'email' => $user->getEmail(),
                'lastname' => $user->getLastname(),
                'firstname' => $user->getFirstname(),
                'country' => $user->getCountryWorkplace(),
                'password' => $password,
                'exist' => $exist,
                'sync' => $sync,
                'reset' => $reset,
            ];
        }

        return $this->render('AppBundle:Account:import.html.twig', ['users' => $importedUsers]);
    }

/**
 * @Route("/test", name="ad_test")
 * @Security("is_granted('ROLE_ADMIN')")
 * @Method({"GET"})
 * @throws \LogicException
 */
    public function testAction()
    {
        $bisUser = $this->get('doctrine.orm.bis_entity_manager')->getRepository('BisBundle:BisPersonView')->getUserByEmail('damien.lagae@btcctb.org');
        $adAccount = $this->get('auth.active_directory')->getUser('damien.lagae@enabel.be');
        $response = $this->get('auth.active_directory')->updateAccount('damien.lagae@enabel.be');

        dump($response);

        exit();
    }
}
