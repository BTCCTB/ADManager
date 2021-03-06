<?php

namespace Auth\Command\Sf;

use Auth\Service\SuccessFactorApi;
use Bis\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class DisableEmployeeCommand extends Command
{
    protected static $defaultName = 'sf:disable-employee';
    /**
     * @var SuccessFactorApi
     */
    private $sfApi;
    /**
     * @var BisPersonView
     */
    private $bisPersonView;

    /**
     * SfDisableEmployeeCommand constructor.
     *
     * @param null             $name
     * @param SuccessFactorApi $sfApi
     */
    public function __construct(SuccessFactorApi $sfApi, BisPersonView $bisPersonView, $name = null)
    {
        $this->sfApi = $sfApi;
        $this->bisPersonView = $bisPersonView;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Disable employees out of contract')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Compare active employee from BIS with SF');
        $io->section('Get active employee from BIS');

        $users = $this->bisPersonView->getActiveUserBySfId();
        $message = sprintf('%d active employee found in BIS !', count($users));
        $io->text($message);
        $io->section('Get not active employee from SF');
        $progressBar = new ProgressBar($io, count($users));
        $disableUsers = $this->sfApi->getInactiveUsers($progressBar);
        $message = sprintf('%d inactive employee found in SF !', count($disableUsers));
        $io->text($message);
        $action = 0;
        foreach ($disableUsers as $id => $data) {
            if (is_int($id) && in_array($id, $users)) {
                $action++;
                $eventData = $this->sfApi->getUserJobHistory($id);
                $message = sprintf(
                    "Employee %d is not active in SF (%s) => End date: %s",
                    $id,
                    $data['active'],
                    $eventData['endDate']
                );
                $io->comment($message);
                $this->bisPersonView->disbaleUserAt($id, $eventData['endDate']);
            }
        }

        if ($action === 0) {
            $io->text('All your active employee found in BIS are OK.');
        } else {
            $message = sprintf('%d active employee found in BIS are inactive in SF.', $action);
            $io->warning($message);
        }

        return 0;
    }
}
