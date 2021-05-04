<?php

namespace Auth\Command\Ad\Account;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Auth\Service\ActiveDirectory;
use Auth\Service\ActiveDirectoryHelper;
use Auth\Service\BisDir;
use Bis\Service\BisPersonView;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ForceExpiredCommand extends Command
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
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * AdFixAttributesCommand constructor.
     *
     * @param ActiveDirectory          $activeDirectory Active directory Service
     * @param BisPersonView            $bisPersonView
     * @param BisDir                   $bisDir
     * @param EntityManagerInterface   $em
     * @param PasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        ActiveDirectory $activeDirectory,
        BisPersonView $bisPersonView,
        BisDir $bisDir,
        EntityManagerInterface $em,
        PasswordEncoderInterface $passwordEncoder
    ) {
        $this->activeDirectory = $activeDirectory;
        $this->bisDir = $bisDir;
        $this->bisPersonView = $bisPersonView;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:account:force-expired')
            ->setDescription('Force expiration for given account')
            ->addArgument(
                'emails',
                InputArgument::OPTIONAL,
                'User(s) email(s)? [...@enabel.be] or [...@enabel.be,...@enabel.be]'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $emails = $input->getArgument('emails');
        $data = [];
        foreach (explode('|', $emails) as $email) {
            // Get user info BIS,AD,LDAP,Password
            $adAccount = $this->activeDirectory->getUser($email);
            $ldapAccount = $this->bisDir->getUser($email);
            /** @var AccountRepository $accountRepository **/
            $accountRepository = $this->em->getRepository(Account::class);
            $accountInfo = $accountRepository->findByEmail($email);

            // Reset Changes logs
            $adChanges = null;
            $accountChanges = null;
            $ldapChanges = null;

            // Generate a new password
            $password = ActiveDirectoryHelper::generatePassword();

            // Set new password in AD
            if ($adAccount !== null) {
                $employeeId = $adAccount->getEmployeeId();
                if (empty($employeeId)) {
                    $employeeId = (int) md5($email);
                }
                $adAccount
                    ->setPassword($password)
                    ->setEmployeeId($employeeId)
                ;
                if ($adAccount->save()) {
                    $adChanges = "Password updated!";

                    // Set new password in passwordAccount
                    if (empty($accountInfo)) {
                        $accountInfo = new Account();

                        $accountInfo
                            ->setEmployeeId($adAccount->getEmployeeId())
                            ->setAccountName($adAccount->getAccountName())
                            ->setUserPrincipalName($adAccount->getUserPrincipalName())
                            ->setEmail($adAccount->getEmail())
                            ->setEmailContact($adAccount->getEmail())
                            ->setFirstname($adAccount->getFirstName())
                            ->setLastname($adAccount->getLastName())
                            ->setGeneratedPassword($password)
                            ->setToken($accountInfo->generateToken($adAccount->getEmail(), $password))
                            ->setActive(true)
                        ;
                        $this->em->persist($accountInfo);
                        $this->em->flush();
                    } else {
                        $accountInfo
                            ->setGeneratedPassword($password)
                            ->setToken($accountInfo->generateToken($adAccount->getEmail(), $password))
                            ->setActive(true)
                        ;
                        $this->em->persist($accountInfo);
                        $this->em->flush();
                    }
                    if ($ldapAccount !== null) {
                        $passwordEncoded = $this->passwordEncoder->encodePassword($password, '');
                        $ldapAccount->setAttribute('userPassword', $passwordEncoded);
                        if ($ldapAccount->save()) {
                            $ldapChanges = "Password updated!";
                        } else {
                            $ldapChanges = "<fg=red> Password not updated!</>";
                        }
                    } else {
                        $ldapChanges = "<fg=yellow>\xE2\x9A\xA1 no ldap account.</>";
                    }
                    $accountChanges = "<info>\xF0\x9F\x97\xB8 Password updated !</info>";
                }
            } else {
                $accountChanges = "<fg=red>\xE2\x9D\x8C AD account not found with this email!</>";
            }
            $data[] = [
                'Account' => $email,
                'AD' => $adChanges,
                'LDAP' => $ldapChanges,
                'New password' => $password,
                'Status' => $accountChanges,
            ];
        }

        $table = new Table($output);
        $table->setHeaders([
            'Account',
            'AD',
            'LDAP',
            'New password',
            'Status',
        ]);

        $table->setRows($data);
        $table->render();

        return 0;
    }
}
