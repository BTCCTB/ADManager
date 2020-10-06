<?php

namespace AuthBundle\Command;

use AlecRabbit\Spinner\SimpleSpinner;
use AuthBundle\Service\ActiveDirectory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdMoveCountryCommand extends Command
{
    protected static $defaultName = 'ad:move:country';
    /**
     * @var ActiveDirectory
     */
    private $ad;

    protected function configure()
    {
        $this
            ->setDescription('Move fields for O365: ad-only vs country');
    }

    public function __construct(ActiveDirectory $ad)
    {
        $this->ad = $ad;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("AD: move fields for O365");
        $io->comment("Move AD-ONLY data to importedFrom field");
        $spinner = new SimpleSpinner();
        $spinner->begin();
        $spinner->message('Get all users from Active Directory')->spin();
        $adUsers = $this->ad->getAllUsers('cn');
        $spinner->message('Find users with AD-ONLY attribute')->spin();
        $migrated = 0;
        $migratedY = 0;
        $migratedN = 0;
        foreach ($adUsers as $adUser) {
            $spinner->spin();
            if ($adUser->getFirstAttribute('physicalDeliveryOfficeName') == 'AD-ONLY') {
                $migrated++;
                $adUser->setAttribute('importedFrom', 'AD-ONLY');
                if ($adUser->save()) {
                    $migratedY++;
                } else {
                    $migratedN++;
                    $io->caution($adUser->getDisplayName() . ' attribute data not migrated !');
                }
            }
        }
        $spinner->end();
        $io->note("User with attribute data to migrate: " . $migrated);
        $io->success("User successfully migrated: " . $migratedY);
        if ($migratedN > 0) {
            $io->success("User not migrated: " . $migratedN);
        }

        return 0;
    }
}
