<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdTestCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * @var ActiveDirectoryNotification
     */
    private $activeDirectoryNotification;

    /**
     * @var BisPersonView
     */
    private $bisPersonView;

    /**
     * AdSyncPhoneCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory Active directory Service
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     * @param BisPersonView               $bisPersonView
     */
    public function __construct(ActiveDirectory $activeDirectory, ActiveDirectoryNotification $activeDirectoryNotification, BisPersonView $bisPersonView)
    {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
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
        $this->setName('ad:test')
            ->setDescription('Test with AD')
            ->addArgument('email', InputArgument::REQUIRED, 'Email to test');
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
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->activeDirectory->getUser($input->getArgument('email'));

        // Test something

        // show result
        $output->writeln('User: ' . $user->getEmail() . ' [' . $user->getEmployeeId() . ']');
    }
}
