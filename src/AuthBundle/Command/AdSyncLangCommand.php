<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponse;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdSyncLangCommand extends Command
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
     * AdSyncLangCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory             Active directory Service
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
        $this->setName('ad:sync:lang')
            ->setDescription('Synchronize language with AD')
            ->addArgument('country', InputArgument::OPTIONAL, 'Country to sync', 'BEL');
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
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('country') !== 'ALL') {
            $bisPersons = $this->bisPersonView->getCountryUsers($input->getArgument('country'));
        } else {
            $bisPersons = $this->bisPersonView->getAllUsers();
        }

        $logs = [];

        foreach ($bisPersons as $bisPerson) {
            if (!empty($bisPerson->getEmail())) {
                $adAccount = $this->activeDirectory->getUser($bisPerson->getEmail());
                if (null !== $adAccount) {
                    $logs[] = $this->activeDirectory->syncLang($adAccount, $bisPerson);
                }
            }
        }

        $this->activeDirectoryNotification->notifyUpdate($logs);

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

        return null;
    }
}
