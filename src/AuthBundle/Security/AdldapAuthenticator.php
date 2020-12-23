<?php

namespace AuthBundle\Security;

use Adldap\Adldap;
use App\Entity\User;
use App\Service\Account;
use AuthBundle\Form\Type\LoginForm;
use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\BisDir;
use AuthBundle\Service\BisDirResponseStatus;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Class AdldapAuthenticator
 *
 * @package AuthBundle\Security
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdldapAuthenticator implements AuthenticatorInterface
{
    use TargetPathTrait;

    private $formFactory;
    private $em;
    private $router;
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;
    /**
     * @var BisDir
     */
    private $bisDir;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Account
     */
    private $account;

    /**
     * AdldapAuthenticator
     *
     * @param FormFactoryInterface $formFactory     The form factory
     * @param EntityManager        $em              The entity manager
     * @param RouterInterface      $router          The router
     * @param UserPasswordEncoder  $passwordEncoder The password encoder
     * @param ActiveDirectory      $activeDirectory The active directory connection service
     * @param BisDir               $bisDir          The ldap [BisDir] connection service
     * @param LoggerInterface      $logger          The logger service
     * @param Account              $account         The account service
     *
     * @phpcs:disable Generic.Files.LineLength
     *
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManager $em, RouterInterface $router, UserPasswordEncoder $passwordEncoder, ActiveDirectory $activeDirectory, BisDir $bisDir, LoggerInterface $logger, Account $account)
    {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->router = $router;
        $this->activeDirectory = $activeDirectory;
        $this->passwordEncoder = $passwordEncoder;
        $this->bisDir = $bisDir;
        $this->logger = $logger;
        $this->account = $account;
    }

    public function supports(Request $request)
    {
        $checkPath = $request->getPathInfo() === '/login';
        $checkMethod = $request->isMethod('POST');

        if (!($checkMethod && $checkPath)) {
            return false;
        }

        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
     *
     * @param Request $request Th request
     *
     * @return mixed
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);
        $data = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * @param mixed         $credentials The credentials [return value from getCredentials()]
     * @param UserInterface $user        The User
     *
     * @return bool
     *
     * @throws \Adldap\Auth\BindException
     * @throws \Adldap\Auth\PasswordRequiredException
     * @throws \Adldap\Auth\UsernameRequiredException
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        $password = $credentials['_password'];
        $username = $credentials['_username'];

        if (false !== $this->activeDirectory->checkUserExistByUsername($username)) {
            $adAccount = $this->activeDirectory->getUser($username);
            $ldapUser = $this->bisDir->getUser($adAccount->getEmail());
            if (null === $ldapUser) {
                $this->bisDir->synchronize($adAccount, $password);
            }
            $log = $this->bisDir->syncPassword($adAccount->getEmail(), $password);
            if ($log->getStatus() !== BisDirResponseStatus::DONE) {
                $this->logger->error($log->getMessage());
            }
            $this->logger->info($log->getMessage());
            $checkCredentials = $this->activeDirectory->checkCredentials($username, $password);
            if (true === $checkCredentials) {
                $this->em->getRepository(User::class)->syncAccount($adAccount, $password, $user);
                $this->account->updateCredentials($adAccount, $password);
            }

            return $checkCredentials;
        }
        throw new BadCredentialsException('Invalid password');
    }

    /**
     * Override to change what happens after successful authentication.
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        $user = $token->getUser();
        $language = 'en';
        if ($user instanceof User) {
            $this->account->lastLogin($user->getEmail());
            $adAccount = $this->activeDirectory->getUser($user->getEmail());
            if (null !== $adAccount) {
                $locale = strtolower(substr($adAccount->getFirstAttribute('preferredLanguage'), 0, 2));
                $language = (in_array($locale, ['en', 'nl', 'fr']))?$locale:'en';
            }
        }
        if ($targetPath = $this->getTargetPath($request->getSession(), 'main')) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('homepage', ["_locale"=>$language]));
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate('security_login');

        return new RedirectResponse($url);
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param mixed                 $credentials  The credentials [return value from getCredentials()]
     * @param UserProviderInterface $userProvider The UserProvider
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws AuthenticationException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        if (null === $username) {
            throw new UsernameNotFoundException('Username can\'t be empty!');
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $username]);

        if (null === $user) {
            $adUser = $this->activeDirectory->getUser($username);

            if (null !== $adUser) {
                $user = new User();
                $user->createFromAD($adUser);
                $this->em->persist($user);
                $this->em->flush();
            }
        }
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $username]);

        if (null === $user) {
            throw new UsernameNotFoundException('User not found!');
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('security_login');

        return new RedirectResponse($url);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * Create an authenticated token for the given user.
     *
     * If you don't care about which token class is used or don't really
     * understand what a "token" is, you can skip this method by extending
     * the AbstractGuardAuthenticator class from your authenticator.
     *
     * @see AbstractGuardAuthenticator
     *
     * @param UserInterface $user
     * @param string        $providerKey The provider (i.e. firewall) key
     *
     * @return PostAuthenticationGuardToken
     *
     * @throws \InvalidArgumentException
     */
    public function createAuthenticatedToken(UserInterface $user, $providerKey)
    {
        return new PostAuthenticationGuardToken(
            $user,
            $providerKey,
            $user->getRoles()
        );
    }
}
