<?php

namespace AuthBundle\Command\Sf;

use AuthBundle\Service\SuccessFactorApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchEmployeeCommand extends Command
{
    protected static $defaultName = 'sf:search-employee';
    /**
     * @var SuccessFactorApi
     */
    private $sfApi;

    /**
     * SfSearchEmployeeCommand constructor.
     *
     * @param null             $name
     * @param SuccessFactorApi $sfApi
     */
    public function __construct(SuccessFactorApi $sfApi, $name = null)
    {
        $this->sfApi = $sfApi;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Search employee in SuccessFactor [GO4HR]')
            ->addArgument('email', InputArgument::REQUIRED, 'User email [@enabel.be]?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if ($email && strpos($email, '@enabel.be')) {
            $message = sprintf('Search employee with email: %s', $email);
            $io->text($message);
            $users = $this->sfApi->searchUsers($email);
            if ($users) {
                $headers = [
                    'id',
                    'lastname',
                    'firstname',
                    'nickname',
                    'gender',
                    'startDate',
                    'endDate',
                    'active',
                    'emailEnabel',
                    'motherLanguage',
                    'preferredLanguage',
                    'phone',
                    'mobile',
                    'position',
                    'jobTitle',
                    'countryWorkplace',
                    'managerId',
                    'jobClass',
                ];
                foreach ($users as $id => $data) {
                    $users[$id]['startDate'] = ($data['startDate'] === null) ? null : $data['startDate']->format('Y-m-d');
                    $users[$id]['endDate'] = ($data['endDate'] === null) ? null : $data['endDate']->format('Y-m-d');
                }
                $io->table($headers, $users);
            } else {
                $io->warning('No users found !');
            }
        } else {
            $io->error('Email should be contains @enabel.be');
        }

        return 0;
    }
}
