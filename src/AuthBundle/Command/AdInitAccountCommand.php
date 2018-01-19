<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponse;
use AuthBundle\Service\BisDir;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdInitAccountCommand extends Command
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
     * @var BisDir
     */
    private $bisDir;

    /**
     * AdResetAccountCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory Active directory Service
     *
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     * @param BisDir                      $bisDir
     */
    public function __construct(ActiveDirectory $activeDirectory, ActiveDirectoryNotification $activeDirectoryNotification, BisDir $bisDir)
    {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
        $this->bisDir = $bisDir;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:init:account')
            ->setDescription('Initialize the AD account with a new password');
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
        $fieldUsers = $this->activeDirectory->getFieldUsers('email');

        // TESTING
        //        $fieldUsers = [];
        //        $fieldUsers[] = $this->activeDirectory->getUser("pierre.dulieu@enabel.be");

        $logs = [];

        /**
         * @var ActiveDirectoryResponse[] $logs
         */
        foreach ($fieldUsers as $fieldUser) {
            $log = $this->activeDirectory->initAccount($fieldUser);
            $data = $log->getData();
            $logs[] = $log;
            $ldapLogs = $this->bisDir->synchronize($fieldUser, $data['password']);
            $logs = array_merge($logs, $ldapLogs);
            $this->activeDirectoryNotification->notifyInitialization($log);
        }

        $this->activeDirectoryNotification->notifyCreation($logs);

        $table = new Table($output);
        $table->setHeaders([
            'message',
            'status',
            'type',
            'data',
        ]);

        $rows = [];

        foreach ($logs as $log) {
            $rows[] = [
                'message' => $log->getMessage(),
                'status' => $log->getStatus(),
                'type' => $log->getType(),
                'data' => json_encode($log->getData()),
            ];
        }
        $table->setRows($rows);
        $table->render();

    }
}
