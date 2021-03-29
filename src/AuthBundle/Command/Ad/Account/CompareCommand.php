<?php

namespace AuthBundle\Command\Ad\Account;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends Command
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
     * @var ActiveDirectoryHelper
     */
    private $activeDirectoryHelper;

    /**
     * AdResetAccountCommand constructor.
     *
     * @param ActiveDirectory       $activeDirectory       Active directory Service
     *
     * @param BisPersonView         $bisPersonView
     * @param ActiveDirectoryHelper $activeDirectoryHelper
     */
    public function __construct(
        ActiveDirectory $activeDirectory,
        BisPersonView $bisPersonView,
        ActiveDirectoryHelper $activeDirectoryHelper
    ) {
        $this->activeDirectory = $activeDirectory;
        $this->bisPersonView = $bisPersonView;
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
        $this->setName('ad:account:compare')
            ->setDescription('Compare the AD account with GO4HR data')
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
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bisUserData = $this->bisPersonView->getUser($input->getArgument('email'));
        $adUserData = $this->activeDirectory->getUser($input->getArgument('email'));
        // Get the correct organizational unit
        $unit = $this->activeDirectory->checkOuExistByName($bisUserData->getCountry());

        // Convert bis user data to ad user data
        $bisAdUserData = $this->activeDirectory->createEmptyUser();
        $bisAdUserData = $this->activeDirectoryHelper::bisPersonToAdUser($bisUserData, $bisAdUserData, $unit);

        $diff = $this->activeDirectoryHelper::compareWithBis($bisAdUserData, $adUserData);

        $rows = [];

        foreach ($diff as $field => $values) {
            $same = "<fg=red>\xF0\x9F\x97\xB4</>";

            if ($values['ad'] === $values['bis']) {
                $same = "<info>\xF0\x9F\x97\xB8</info>";
            }
            $rows[$field] = [
                'field' => ucfirst($field),
                'AD' => $values['ad'],
                'BIS' => $values['bis'],
                'Same' => $same,
            ];
        }

        $output->writeln('Account: ' . $input->getArgument('email'));

        $table = new Table($output);
        $table->setHeaders([
            'Field',
            'AD',
            'BIS',
            'Same',
        ]);

        $table->addRows($rows);

        $table->render();

        return 0;
    }
}
