<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
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
     * @var ActiveDirectoryHelper
     */
    private $activeDirectoryHelper;

    /**
     * AdSyncPhoneCommand constructor.
     *
     * @param ActiveDirectory             $activeDirectory Active directory Service
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     * @param BisPersonView               $bisPersonView
     * @param BisDir                      $bisDir
     */
    public function __construct(ActiveDirectory $activeDirectory, ActiveDirectoryNotification $activeDirectoryNotification, BisPersonView $bisPersonView, BisDir $bisDir, ActiveDirectoryHelper $activeDirectoryHelper)
    {
        $this->activeDirectory = $activeDirectory;
        $this->activeDirectoryNotification = $activeDirectoryNotification;
        $this->bisPersonView = $bisPersonView;
        $this->bisDir = $bisDir;
        $this->activeDirectoryHelper = $activeDirectoryHelper;
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
        $email = 'damien.lagae@enabel.be';
//        $bisPerson = $this->bisPersonView->getUser($email);
        //$adUser = $this->activeDirectory->getUser($email);
        //        $adUser = $this->activeDirectory->getAd()->make()->user();
        // Get the correct organizational unit
        //        $organizationalUnit = $this->activeDirectory->checkOuExistByName($bisPerson->getCountry());
        //        $adUser = $this->activeDirectoryHelper::bisPersonToAdUser($bisPerson, $adUser, $organizationalUnit);
        //        var_dump($adUser);
        //        list($adAccount, $diffData) = $this->activeDirectoryHelper::bisPersonUpdateAdUser($bisPerson, $adUser);
        //        var_dump($diffData);

        $logs[] = $this->activeDirectory->updateAccount($email);

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
}
