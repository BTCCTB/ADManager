<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdSyncBisToADCommand extends Command
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
        $this->setName('ad:sync:bistoad')
            ->setDescription('Sync the BIS account with AD account')
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
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bisPersonView = $this->bisPersonView
            ->getUser($input->getArgument('email'));

        if (null !== $bisPersonView) {
            $responses = $this->activeDirectory
                ->synchronizeFromBis($bisPersonView);

            $output->writeln('User: ' . $bisPersonView->getEmail() . ' - ' . $bisPersonView->getDisplayName());
            foreach ($responses as $response) {
                $output->writeln('Message: ' . $response->getMessage());
                $output->writeln('Status: ' . $response->getStatus());
                $output->writeln('Type: ' . $response->getType());
                $output->writeln('Data: ' . json_encode($response->getData()));
            }
        }

        return null;
    }
}
