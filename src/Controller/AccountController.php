<?php

namespace App\Controller;

use Adldap\Models\User;
use App\Entity\Account;
use App\Form\ActionAuthType;
use App\Form\ChangePasswordType;
use App\Repository\AccountRepository;
use App\Service\Account as AccountService;
use App\Service\SecurityAudit;
use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponseStatus;
use BisBundle\Service\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/", name="account_list")
     * @IsGranted("ROLE_ADMIN")
     * @Method({"GET"})
     * @param AccountRepository $accountRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(AccountRepository $accountRepository)
    {
        $accounts = $accountRepository->findAllActive();

        return $this->render('Account/index.html.twig', ['accounts' => $accounts]);
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

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($ad->checkCredentials($user->getEmail(), $data['current_password'])) {
                $passwordCheck = ActiveDirectoryHelper::checkPasswordComplexity($data['password']);
                if ($passwordCheck === true) {
                    if ($ad->changePassword($user->getEmail(), $data['password'])) {
                        if ($bisdir->syncPassword($user->getEmail(), $data['password'])) {
                            $this->get(SecurityAudit::class)->changePassword(
                                $this->get(AccountService::class)->getAccount($user->getEmail()),
                                $this->get('security.token_storage')->getToken()->getUser()
                            );
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
     * @IsGranted("ROLE_SUPER_ADMIN")
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
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] disabled!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_list');
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

        return $this->redirectToRoute('account_list');
    }

    /**
     * @Route("/reset/{employeeID}", name="account_reset_password")
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
        $adNotification = $this->get(ActiveDirectoryNotification::class);
        $user = $ad->checkUserExistByEmployeeID($employeeID);
        $em = $this->get('doctrine.orm.default_entity_manager');
        $accountRepository = $em->getRepository(Account::class);
        $account = $accountRepository->find($employeeID);

        $resetPassword = $ad->initAccount($user);

        if ($resetPassword->getStatus() === ActiveDirectoryResponseStatus::DONE) {
            $adNotification->notifyInitialization($resetPassword);
            $this->get(SecurityAudit::class)->resetPassword($account, $this->get('security.token_storage')->getToken()->getUser());
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] initialized!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_list');
    }

    /**
     * @Route("/check/{id}", name="account_check_password")
     * @ParamConverter("id", class="App:Account")
     * @IsGranted("ROLE_ADMIN")
     * @Method({"GET","POST"})
     * @param Account         $account The account to test
     * @param Request         $request The request (Form data)
     * @param ActiveDirectory $ad The Active Directory Service
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPasswordAction(Account $account, Request $request, ActiveDirectory $ad)
    {
        $form = $this->createForm(ActionAuthType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($ad->checkCredentials($account->getEmail(), $data['password'])) {
                $this->get(SecurityAudit::class)->testPassword($account, $this->get('security.token_storage')->getToken()->getUser(), true);
                $this->addFlash('success', 'This password is correct !');
            } else {
                $this->get(SecurityAudit::class)->testPassword($account, $this->get('security.token_storage')->getToken()->getUser(), false);
                $this->addFlash('danger', 'This password don\'t match !');
            }
        }

        return $this->render('Account/checkPassword.html.twig', ['form' => $form->createView(), 'account' => $account]);
    }

    /**
     * @Route("/detail/{id}", name="account_detail")
     * @ParamConverter("id", class="App:Account")
     * @IsGranted("ROLE_ADMIN")
     * @Method({"GET"})
     * @param Account         $account          The account to test
     * @param ActiveDirectory $ad               The Active Directory Service
     * @param BisPersonView   $bisPersonView    The BIS Person View Service
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Account $account, ActiveDirectory $ad, BisPersonView $bisPersonView)
    {
        if (!empty($account->getEmail())) {
            $adUser = $ad->getUser($account->getEmail());
            $bisUser = $bisPersonView->getUser($account->getEmail());
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
            return $this->redirectToRoute('account_list');
        }

        return $this->render('Account/detail.html.twig', ['account' => $account, 'adData' => $adUser, 'bisData' => $bisUser]);
    }
}
