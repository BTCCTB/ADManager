<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdGhostAccountCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * AdResetAccountCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory Active directory Service
     *
     */
    public function __construct(ActiveDirectory $activeDirectory)
    {
        $this->activeDirectory = $activeDirectory;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:ghost:account')
            ->setDescription('Check the AD account with GO4HR data and report Ghost AD account');
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
        $logs = $this->activeDirectory->ghostAccount();

        $table = new Table($output);
        $table->setHeaders([
            'Email',
            'AD account',
            'DN',
            'Start date',
            'End date',
            'Message',
        ]);

        $table->addRows($logs);

        $table->render();

    }
}
