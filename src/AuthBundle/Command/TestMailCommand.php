<?php

namespace AuthBundle\Command;

use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\ActiveDirectoryHelper;
use AuthBundle\Service\ActiveDirectoryNotification;
use AuthBundle\Service\ActiveDirectoryResponse;
use AuthBundle\Service\ActiveDirectoryResponseStatus;
use AuthBundle\Service\ActiveDirectoryResponseType;
use BisBundle\Entity\BisCountry;
use BisBundle\Entity\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMailCommand extends Command
{
    /**
     * @var ActiveDirectoryNotification
     */
    private $activeDirectoryNotification;

    /**
     * AdFixNameCommand constructor.
     *
     * @param ActiveDirectoryNotification $activeDirectoryNotification
     */
    public function __construct(ActiveDirectoryNotification $activeDirectoryNotification)
    {
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
        $this->setName('ad:test:mail')
            ->setDescription('Test send notification mail.');
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bisCountry = new BisCountry();
        $bisCountry->setCouId(761)
            ->setCouId(504)
            ->setCouName('Morocco')
            ->setCouIsocode2letters('MA')
            ->setCouIsocode3letters('MAR');
        $bisPersonView = new BisPersonView();

        $bisPersonView->setFirstname('Test')
            ->setLastname('Test')
            ->setEmail('test@enabel.be')
            ->setActive(1)
            ->setCountryWorkplace($bisCountry)
            ->setFunction('Testeur')
            ->setId(987654)
            ->setJobClass('Expats')
            ->setLanguage('FR')
            ->setDateContractStart(new \DateTime('2010-01-01'))
            ->setDateContractStop(new \DateTime('2020-12-31'))
            ->setSex('M');

        $logs[] = new ActiveDirectoryResponse(
            'test',
            ActiveDirectoryResponseStatus::DONE,
            ActiveDirectoryResponseType::CREATE,
            ActiveDirectoryHelper::getDataBisUser($bisPersonView)
        );

        $this->activeDirectoryNotification->notifyCreation($logs);
    }

}
