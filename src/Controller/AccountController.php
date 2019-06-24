<?php

namespace App\Controller;

use Adldap\Models\User;
use App\Entity\Account;
use App\Form\ActionAuthType;
use App\Form\ChangeEmailType;
use App\Form\ChangePasswordType;
use App\Repository\AccountRepository;
use App\Repository\UserRepository;
use App\Service\Account as AccountService;
use App\Service\SecurityAudit;
use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponseStatus;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AccountController
 *
 * @package Controller
 *
 * @author  Damien Lagae <damienlagae@gmail.com>
 *
 * @Route("/account")
 *
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;
    /**
     * @var BisDir
     */
    private $bisDir;
    /**
     * @var AccountService
     */
    private $accountService;
    /**
     * @var SecurityAudit
     */
    private $securityAudit;

    public function __construct(ActiveDirectory $activeDirectory, BisDir $bisDir, AccountService $accountService, SecurityAudit $securityAudit)
    {

        $this->activeDirectory = $activeDirectory;
        $this->bisDir = $bisDir;
        $this->accountService = $accountService;
        $this->securityAudit = $securityAudit;
    }

    /**
     * @Route("/my", name="my_account")
     *
     * @throws \LogicException
     *
     * @IsGranted("ROLE_USER")
     */
    public function myAction()
    {
        $user = $this->activeDirectory->checkUserExistByUsername($this->getUser()->getUsername());

        $now = new \DateTime('now');
        $passwordLastSet = new \DateTime();
        $passwordLastSet->setTimestamp($user->getPasswordLastSetTimestamp());

        $passwordAges = $passwordLastSet->diff($now)->format('%a');

        return $this->render(
            'Account/myAccount.html.twig',
            [
                'user' => $user,
                'country' => $user->getFirstAttribute('c'),
                'passwordAges' => $passwordAges,
            ]
        );
    }

    /**
     * @Route("/", name="account_list", methods={"GET"})
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param AccountRepository $accountRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(AccountRepository $accountRepository)
    {
        $accounts = $accountRepository->findAllActive();

        return $this->render('Account/index.html.twig', ['accounts' => $accounts]);
    }

    /**
     * @Route("/change-password", name="account_change_password", methods={"GET","POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Adldap\AdldapException
     */
    public function changeAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($this->activeDirectory->checkCredentials($user->getEmail(), $data['current_password'])) {
                $passwordCheck = ActiveDirectoryHelper::checkPasswordComplexity($data['password']);
                if (true === $passwordCheck) {
                    if ($this->activeDirectory->changePassword($user->getEmail(), $data['password'])) {
                        if ($this->bisDir->syncPassword($user->getEmail(), $data['password'])) {
                            $this->securityAudit->changePassword(
                                $this->accountService->getAccount($user->getEmail()),
                                $user
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
     * @Route("/disable/{employeeID}", name="ad_disable_account", methods={"GET"})
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @param integer $employeeID The employee ID
     *
     * @return RedirectResponse
     *
     * @throws \LogicException
     */
    public function disableAction($employeeID): RedirectResponse
    {
        /**
         * @var User $user
         */
        $user = $this->activeDirectory->checkUserExistByEmployeeID($employeeID);
        if ($this->activeDirectory->disableUser($user)) {
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] disabled!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_list');
    }

    /**
     * @Route("/enable/{employeeID}", name="ad_enable_account", methods={"GET"})
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param integer $employeeID The employee ID
     *
     * @return RedirectResponse
     *
     * @throws \LogicException
     */
    public function enableAction($employeeID): RedirectResponse
    {
        $user = $this->activeDirectory->checkUserExistByEmployeeID($employeeID);

        if ($this->activeDirectory->enableUser($user)) {
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] enabled!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_list');
    }

    /**
     * @Route("/reset/{employeeID}", name="account_reset_password", methods={"GET"})
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param integer                     $employeeID                  The employee ID     *
     * @param AccountRepository           $accountRepository
     *
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     *
     * @return RedirectResponse
     *
     * @throws \Adldap\AdldapException
     */
    public function resetAction($employeeID, AccountRepository $accountRepository, ActiveDirectoryNotification $activeDirectoryNotification): RedirectResponse
    {
        $user = $this->activeDirectory->checkUserExistByEmployeeID($employeeID);
        $account = $accountRepository->find($employeeID);

        $resetPassword = $this->activeDirectory->initAccount($user);

        if ($resetPassword->getStatus() === ActiveDirectoryResponseStatus::DONE) {
//            $activeDirectoryNotification->notifyInitialization($resetPassword);
            $this->securityAudit->resetPassword($account, $this->get('security.token_storage')->getToken()->getUser());
            $this->addFlash('success', 'Account [' . $user->getUserPrincipalName() . '] initialized!');
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');
        }

        return $this->redirectToRoute('account_list');
    }

    /**
     * @Route("/check/{id}", name="account_check_password", methods={"GET","POST"})
     *
     * @ParamConverter("id", class="App:Account")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Account $account The account to test
     * @param Request $request The request (Form data)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkPasswordAction(Account $account, Request $request)
    {
        $form = $this->createForm(ActionAuthType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($this->activeDirectory->checkCredentials($account->getEmail(), $data['password'])) {
                $this->securityAudit->testPassword($account, $this->get('security.token_storage')->getToken()->getUser(), true);
                $this->addFlash('success', 'This password is correct !');
            } else {
                $this->securityAudit->testPassword($account, $this->get('security.token_storage')->getToken()->getUser(), false);
                $this->addFlash('danger', 'This password don\'t match !');
            }
        }

        return $this->render('Account/checkPassword.html.twig', ['form' => $form->createView(), 'account' => $account]);
    }

    /**
     * @Route("/detail/{id}", name="account_detail", methods={"GET"})
     *
     * @ParamConverter("id", class="App:Account")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Account       $account       The account to test     *
     * @param BisPersonView $bisPersonView
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(Account $account, BisPersonView $bisPersonView)
    {
        if (!empty($account->getEmail())) {
            $adUser = $this->activeDirectory->getUser($account->getEmail());
            $ldapUser = $this->bisDir->getUser($account->getEmail());
            $bisData = $bisPersonView->getUserData($account->getEmail());
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');

            return $this->redirectToRoute('account_list');
        }

        return $this->render('Account/detail.html.twig', ['account' => $account, 'adData' => $adUser, 'ldapData' => $ldapUser, 'bisData' => $bisData]);
    }

    /**
     * @Route("/change-email/{id}", name="account_change_email", methods={"GET","POST"})
     *
     * @ParamConverter("id", class="App:Account")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Account           $account           The account to test
     * @param Request           $request
     * @param UserRepository    $userRepository
     * @param AccountRepository $accountRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function changeEmailAction(Account $account, Request $request, UserRepository $userRepository, AccountRepository $accountRepository)
    {
        if (!empty($account->getEmail())) {
            $form = $this->createForm(ChangeEmailType::class);
            $form->handleRequest($request);
            $adData = $this->activeDirectory->getUser($account->getEmail());

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $emailStringRule = '((?:[a-z][a-z]+))'; // Firstname
                $emailStringRule .= '(\\.)'; // Separator
                $emailStringRule .= '((?:[a-z][a-z]+))'; // Lastname
                $emailStringRule .= '(@enabel\\.be)'; // Fully Qualified Domain Name

                if ($c = preg_match_all("/" . $emailStringRule . "/is", $data['new_email'], $matches)) {
                    // sanitize email
                    $email = strtolower(trim($data['new_email']));

                    // Apply email change in AD
                    $adUser = $this->activeDirectory->findAndChangeEmail($account->getEmail(), $email, $data['keep_proxy']);

                    if (null !== $adUser && $adUser->getEmail() == $email) {
                        // Apply email change in LDAP
                        $this->bisDir->findAndChangeEmail($account->getEmail(), $email);
                        // Apply email change in User DB
                        $userRepository->changeEmail($account->getAccountName(), $email);
                        // Apply email change in Account DB
                        $account = $accountRepository->changeEmail($account, $email);

                        return $this->redirectToRoute('account_detail', ['id' => $account->getEmployeeId()]);
                    }
                    $form->addError(new FormError('The email address can\' t be changed'));
                } else {
                    $form->get('new_email')->addError(new FormError('The new email address must be a valid Enabel email address [firstname.lastname@enabel.be]'));
                }
            }
        } else {
            $this->addFlash('danger', 'Can\'t do this action!');

            return $this->redirectToRoute('account_list');
        }

        return $this->render('Account/changeEmail.html.twig', ['form' => $form->createView(), 'account' => $account, 'adData' => $adData]);
    }
}
