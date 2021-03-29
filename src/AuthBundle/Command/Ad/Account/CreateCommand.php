<?php

namespace AuthBundle\Command\Ad\Account;

use Adldap\Models\Attributes\DistinguishedName;
use App\Entity\Account;
use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponseStatus;
use AuthBundle\Service\ActiveDirectoryResponseType;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
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
     * @var BisPersonView
     */
    private $bisPersonView;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdResetAccountCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory Active directory Service
     * @param BisDir          $bisDir
     * @param BisPersonView   $bisPersonView
     * @param EntityManagerInterface   $em
     */
    public function __construct(
        ActiveDirectory $activeDirectory,
        BisDir $bisDir,
        BisPersonView $bisPersonView,
        EntityManagerInterface $em
    ) {
        $this->activeDirectory = $activeDirectory;
        $this->bisDir = $bisDir;
        $this->bisPersonView = $bisPersonView;
        $this->em = $em;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:account:create')
            ->setDescription('Create the AD account with GO4HR data')
            ->addArgument('emails', InputArgument::REQUIRED, 'User email [@enabel.be]?');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $emails = $input->getArgument('emails');
        foreach (explode('|', $emails) as $email) {
            $bisPersonView = $this->bisPersonView
                ->getUser($email);

            if (null !== $bisPersonView) {
                $adUser = $this->activeDirectory->createEmptyUser();
                $adUser->setFirstName($bisPersonView->getFirstname())
                    ->setLastName($bisPersonView->getLastname())
                    ->setEmail($bisPersonView->getEmail())
                    ->setInitials($bisPersonView->getInitials())
                    ->setDisplayName($bisPersonView->getDisplayName())
                    ->setCommonName($bisPersonView->getCommonName())
                    ->setUserPrincipalName($bisPersonView->getUserPrincipalName())
                    ->setAccountName($bisPersonView->getAccountName());

                // Get or create the country OU
                $ou = $this->activeDirectory->checkOuExistByName($bisPersonView->getCountry());
                $dn = new DistinguishedName();
                $dn->setBase($ou->getDn());
                $dn->addCn($adUser->getCommonName());
                $adUser->setDn($dn);

                // Password
                $password = ActiveDirectoryHelper::generatePassword();
                $adUser->setPassword($password);

                if (!$adUser->save()) {
                    $output->writeln('User: ' . $bisPersonView->getEmail() . ' - ' . $bisPersonView->getDisplayName());
                    $output->writeln('Message: Unable to create and set "' . $password . '" as default password');
                    $output->writeln('Status: ' . ActiveDirectoryResponseStatus::EXCEPTION);
                    $output->writeln('Type: ' . ActiveDirectoryResponseType::CREATE);
                }

                $output->writeln('User: ' . $bisPersonView->getEmail() . ' - ' . $bisPersonView->getDisplayName());
                $output->writeln('Message: Created with "' . $password . '" as default password');
                $output->writeln('Status: ' . ActiveDirectoryResponseStatus::DONE);
                $output->writeln('Type: ' . ActiveDirectoryResponseType::CREATE);

                // Update account list with generated password
                $accountRepository = $this->em->getRepository(Account::class);
                $account = $accountRepository->findOneBy([
                    'email' => $bisPersonView->getEmail(),
                ]);

                if (empty($account)) {
                    $account = new Account();
                    $account->setEmployeeId($bisPersonView->getEmployeeId())
                        ->setAccountName($bisPersonView->getAccountName())
                        ->setUserPrincipalName($bisPersonView->getUserPrincipalName())
                        ->setEmail($bisPersonView->getEmail())
                        ->setEmailContact($bisPersonView->getEmail())
                        ->setFirstname($bisPersonView->getFirstName())
                        ->setLastname($bisPersonView->getLastName())
                        ->setGeneratedPassword($password)
                        ->setToken($account->generateToken($account->getEmail(), $password))
                        ->setActive(true);
                } else {
                    $account->setGeneratedPassword($password)
                        ->setToken($account->generateToken($account->getEmail(), $password))
                        ->setActive(true);
                }
            }
        }

        return 0;
    }
}
