<?php

namespace AppBundle\Controller;

use Adldap\Models\User;
use BisBundle\Entity\BisPersonView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AccountController
 *
 * @package AppBundle\Controller
 * @author  Damien Lagae <damienlagae@gmail.com>
 * @Route("/account")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="account_ad_list")
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
     * @Route("/bis", name="account_bis_list")
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
     * @Route("/synchronize/{user_account}", name="ad_synchronize_account")
     * @Method({"GET"})
     * @param String $user_account The account name
     *
     * @return RedirectResponse
     * @throws \LogicException
     */
    public function synchronizeAccountAction($user_account): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager('bis');
        $ad = $this->get('auth.active_directory');
        // Get Users from BIS
        $users = $em->getRepository('BisBundle:BisPersonView')->getUserByUsername($user_account);

        var_dump($users);exit();
        if ($ad->syncAdUser($user_account)) {
            //TODO: flash message OK
        } else {
            //TODO: flash message NOT OK
        }

        return $this->redirectToRoute('account_ad_list');
    }

    /**
     * @Route("/import/{country_code}", name="ad_import_account")
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
     * @Method({"GET"})
     * @throws \LogicException
     */
    public function testAction()
    {
        $ad = $this->get('auth.active_directory');
//        $user = $ad->checkUserExistByUsername('david.monnoye@enabel.be');

        $bisPersonViewRepository = $this->get('doctrine.orm.bis_entity_manager')->getRepository('BisBundle:BisPersonView');
        //TODO: find all users and fill departement with country workplace.
        $bisPersonViews = $bisPersonViewRepository->findAllFieldUser();

        echo "<table border='1'>" .
            "<thead>" .
            "<tr>" .
            "<th>Email</th>" .
            "<th>Account AD</th>" .
            "<th>Country</th>" .
            "<th>Update</th>" .
            "</tr>" .
            "</thead>";
        /**
         * @var BisPersonView $bisPersonView
         */
        foreach ($bisPersonViews as $bisPersonView) {
            $user = $ad->checkUserExistByEmail($bisPersonView->getEmail());
            if ($user instanceof User) {
                if (!empty($bisPersonView->getEmail())) {
//                    $user->setUserPrincipalName($bisPersonView->getDomainAccount());
                    $user->setDepartment($bisPersonView->getCountryWorkplace());
                    $user->setEmployeeId($bisPersonView->getId());
                    if (!empty($bisPersonView->getFunction())) {
                        $user->setTitle(substr($bisPersonView->getFunction(), 0, 60));
                        $user->setDescription(substr($bisPersonView->getFunction(), 0, 100));
                    }
                    $user->setProxyAddresses([
                        'SMTP:' . $bisPersonView->getUsername() . '@enabel.be',
                        'smtp:' . $bisPersonView->getUsername() . '@btcctb.org',
                    ]);

                    $user->setCompany('Enabel');
                    if ($user->save()) {
                        echo "<tr><td>" . $bisPersonView->getEmail() . "</td><td>" . $bisPersonView->getDomainAccount() . "</td><td>" . $bisPersonView->getCountryWorkplace() . "</td><td>OK</td></tr>";
                    } else {
                        echo "<tr><td>" . $bisPersonView->getEmail() . "</td><td>" . $bisPersonView->getDomainAccount() . "</td><td>" . $bisPersonView->getCountryWorkplace() . "</td><td>NOT OK</td></tr>";
                    }
                }
            }
        }
        echo "</table>";
        exit();
    }
}
