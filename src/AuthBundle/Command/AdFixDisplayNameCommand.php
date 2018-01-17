<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdFixDisplayNameCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * AdFixNameCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory Active directory Service
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
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
        $this->setName('ad:fix:displayname')
            ->setDescription('Fix the display name in the AD with GO4HR data.');
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = $this->activeDirectory->fixDisplayName();

        $table = new Table($output);
        $table->setHeaders([
            'User',
            'Current data',
            'New data',
            'State',
        ]);
        $table->setRows($log);
        $table->render();

    }

}