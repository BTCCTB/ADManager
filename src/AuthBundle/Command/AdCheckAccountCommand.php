<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdCheckAccountCommand extends Command
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
     * AdResetAccountCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory Active directory Service
     *
     * @param BisPersonView   $bisPersonView
     * @param BisDir          $bisDir
     */
    public function __construct(ActiveDirectory $activeDirectory, BisPersonView $bisPersonView, BisDir $bisDir)
    {
        $this->activeDirectory = $activeDirectory;
        $this->bisDir = $bisDir;
        $this->bisPersonView = $bisPersonView;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:check:account')
            ->setDescription('Check the AD account with GO4HR data')
            ->addArgument('email', InputArgument::REQUIRED, 'User email [@enabel.be]?');
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
     * @return void null or 0 if everything went fine, or an error code
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adAccount = $this->activeDirectory
            ->getUser($input->getArgument('email'));
        $ldapAccount = $this->bisDir
            ->getUser($input->getArgument('email'));
        $bisPerson = $this->bisPersonView
            ->getUser($input->getArgument('email'));

        $fieldAd = [
            'employeeID' => 'employeeid',
            'firstname' => 'givenname',
            'lastname' => 'sn',
            'email' => 'mail',
            'displayName' => 'displayname',
            'sex' => 'initials',
            'country' => 'co',
            'function' => 'description',
        ];
        $fieldLdap = [
            'employeeID' => 'employeenumber',
            'firstname' => 'givenname',
            'lastname' => 'sn',
            'email' => 'uid',
            'displayName' => 'displayname',
            'sex' => 'initials',
            'country' => 'c',
            'function' => 'title',
        ];
        $fieldBis = [
            'employeeID' => 'per_id',
            'firstname' => 'per_firstname',
            'lastname' => 'per_lastname',
            'email' => 'per_email',
            'displayName' => 'displayname',
            'sex' => 'per_sex',
            'country' => 'co',
            'function' => 'per_function',
        ];

        $rows = [];

        foreach ($fieldAd as $field => $adAttributes) {
            $ad = "/";
            $ldap = "/";
            $bis = "/";
            $sync = "<fg=red>\xF0\x9F\x97\xB4</>";
            if ($adAccount !== null) {
                $ad = $adAccount->getFirstAttribute($adAttributes);
            }
            if ($ldapAccount !== null) {
                $ldap = $ldapAccount->getFirstAttribute($fieldLdap[$field]);
            }
            if ($bisPerson !== null) {
                $bis = $bisPerson->getAttribute($fieldBis[$field]);
            }

            if ($bis === $ad && $bis === $ldap) {
                $sync = "<info>\xF0\x9F\x97\xB8</info>";
            }
            $rows[$field] = [
                'field' => ucfirst($field),
                'AD' => $ad,
                'LDAP' => $ldap,
                'BIS' => $bis,
                'Sync' => $sync,
            ];
        }

        if ($bisPerson !== null) {
            $output->writeln('BIS: ' . $bisPerson->getEmail() . ' - ' . $bisPerson->getDisplayName());
        }

        $table = new Table($output);
        $table->setHeaders([
            'Field',
            'AD',
            'LDAP',
            'BIS',
            'Sync',
        ]);

        $table->addRows($rows);

        $table->render();

    }
}