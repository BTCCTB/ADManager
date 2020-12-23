<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\SuccessFactorApi;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdSetThumbnailCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * @var BisPersonView
     */
    private $bisPersonView;
    /**
     * @var SuccessFactorApi
     */
    private $SFApi;

    /**
     * AdFixAttributesCommand constructor.
     *
     * @param ActiveDirectory  $activeDirectory Active directory Service
     *
     * @param BisPersonView    $bisPersonView
     * @param SuccessFactorApi $SFApi
     */
    public function __construct(ActiveDirectory $activeDirectory, BisPersonView $bisPersonView, SuccessFactorApi $SFApi)
    {
        $this->activeDirectory = $activeDirectory;
        $this->bisPersonView = $bisPersonView;
        $this->SFApi = $SFApi;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:set:thumbnail')
            ->setDescription('Get picture from GO4HR API & set this as AD thumbnail')
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders([
            'Account',
            'ID',
            'API Status',
            'AD Status',
        ]);
        $data = [];
        $status = null;

        $adAccount = $this->activeDirectory->getUser($input->getArgument('email'));
        $userID = $adAccount->getEmployeeId();
        $thumbnailFromSF = $this->SFApi->getUserPicture($userID);
        if (null !== $thumbnailFromSF) {
            $adAccount->setThumbnail($thumbnailFromSF);
            $status = $adAccount->save();
        }

        $data[] = [
            $input->getArgument('email'),
            $userID,
            $thumbnailFromSF !== null,
            $status,
        ];

        $table->setRows($data);
        $table->render();

        return 0;
    }
}
