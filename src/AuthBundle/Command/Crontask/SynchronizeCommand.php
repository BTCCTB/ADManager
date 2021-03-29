<?php

namespace AuthBundle\Command\Crontask;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponse;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeCommand extends Command
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
     * AdFixNameCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory             Active directory Service
     *
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     */
    public function __construct(
        ActiveDirectory $activeDirectory,
        ActiveDirectoryNotification $activeDirectoryNotification
    ) {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('crontask:synchronize')
            ->setDescription('Synchronise the AD with GO4HR data.');
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
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var ActiveDirectoryResponse[] $logs
         */
        $logs = $this->activeDirectory->cronTaskSynchronize();

        $table = new Table($output);
        $table->setHeaders([
            'message',
            'status',
            'type',
            'data',
        ]);

        $i = 0;
        foreach ($logs as $log) {
            $table->setRow($i, [
                'message' => $log->getMessage(),
                'status' => $log->getStatus(),
                'type' => $log->getType(),
                'data' => json_encode($log->getData()),
            ]);
            $i++;
        }
        $table->render();

        $this->activeDirectoryNotification->notifyCreation($logs);
        $this->activeDirectoryNotification->notifyMove($logs);
        $this->activeDirectoryNotification->notifyUpdate($logs);
        $this->activeDirectoryNotification->notifyDisabled($logs);

        return 0;
    }
}
