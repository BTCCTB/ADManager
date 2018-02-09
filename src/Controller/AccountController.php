<?php

namespace App\Controller;

use Adldap\Models\User;
use AuthBundle\Form\ChangePasswordForm;
use AuthBundle\Service\ActiveDirectoryHelper;
use AuthBundle\Service\ActiveDirectoryResponseStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountController
 *
 * @package Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @Route("/account")
 * @IsGranted("ROLE_USER")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="account_ad_list")
     * @IsGranted("ROLE_ADMIN")
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
        return $this->render('Account/index.html.twig', ['accounts' => $accounts, 'country_distribution' => $countryStats]);
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

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($ad->checkCredentials($user->getEmail(), $data['current_password'])) {
                $passwordCheck = ActiveDirectoryHelper::checkPasswordComplexity($data['password']);
                if ($passwordCheck === true) {
                    if ($ad->changePassword($user->getEmail(), $data['password'])) {
                        if ($bisdir->syncPassword($user->getEmail(), $data['password'])) {
                            $this->addFlash('success', 'Password successfully changed !');
                            return $this->redirectToRoute('homepage');
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

        return $this->render('Account/change.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/disable/{employeeID}", name="ad_disable_account")
     * @IsGranted("ROLE_ADMIN")
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
     * @IsGranted("ROLE_ADMIN")
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
     * @Route("/reset/{employeeID}", name="ad_reset_account")
     * @IsGranted("ROLE_ADMIN")
     * @Method({"GET"})
     * @param integer $employeeID The employee ID
     *
     * @return RedirectResponse
     * @throws \LogicException
     * @throws \Adldap\AdldapException
     */
    public function resetAction($employeeID): RedirectResponse
    {
        $ad = $this->get('auth.active_directory');
        $adNotification = $this->get('AuthBundle\Service\ActiveDirectoryNotification');
        $user = $ad->checkUserExistByEmployeeID($employeeID);

        $resetPassword = $ad->initAccount($user);

        if ($resetPassword->getStatus() === ActiveDirectoryResponseStatus::DONE) {
            $adNotification->notifyInitialization($resetPassword);
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] initialized!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_ad_list');
    }
}
