<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
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
     * @var BisDir
     */
    private $bisDir;

    /**
     * AdSyncPhoneCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory Active directory Service
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     * @param BisPersonView               $bisPersonView
     * @param BisDir                      $bisDir
     */
    public function __construct(ActiveDirectory $activeDirectory, ActiveDirectoryNotification $activeDirectoryNotification, BisPersonView $bisPersonView, BisDir $bisDir)
    {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
        $this->bisPersonView = $bisPersonView;
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
        $this->setName('ad:test')
            ->setDescription('Test with AD');
//        $this->addArgument('email', InputArgument::REQUIRED, 'Email to test');
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
        $outputStyle = new OutputFormatterStyle('red', null, array('bold'));
        $output->getFormatter()->setStyle('warning', $outputStyle);

        $bisPersons = $this->bisPersonView->getAllUsers();

        $logs = $this->bisDir->enableFromBis($bisPersons);

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
    }

//        $user = $this->activeDirectory->getUser($input->getArgument('email'));
    //        $bisUsers = $this->bisPersonView->getCountryUsers();
    //
    //        foreach ($bisUsers as $bisUser) {
    //            if ($bisUser->getEmail() !== null) {
    //                $user = $this->activeDirectory->getUser($bisUser->getEmail());
    //                if ($user !== null) {
    //                    $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT | AccountControl::DONT_EXPIRE_PASSWORD);
    //                    $user->setAccountExpiry(null);
    //                    $user->save();
    //                    if (!$user->save()) {
    //                        $output->writeln('<error> Unable to change User Account Control for this user : ' . $user->getEmail() . ' [' . $user->getEmployeeId() . ']</error>');
    //                    } else {
    //                        $output->writeln('<info> User Account Control updated for this user : ' . $user->getEmail() . ' [' . $user->getEmployeeId() . ']</info>');
    //                    }
    //                } else {
    //                    $output->writeln('<error> User not found: ' . $bisUser->getEmail() . ' [' . $bisUser->getEmployeeId() . ']</error>');
    //                }
    //            } else {
    //                $output->writeln('<warning> User without email : ' . $bisUser->getFirstname() . ' ' . $bisUser->getLastname() . ' [' . $bisUser->getEmployeeId() . ']</warning>');
    //            }
    //
    //        }

    // Test something

    // show result
    //        $output->writeln('<info>User: ' . $user->getEmail() . ' [' . $user->getEmployeeId() . ']</info>');
    //    }
}
