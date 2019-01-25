<?php

namespace AuthBundle\Command;

use Adldap\Models\User;
use AuthBundle\Service\ActiveDirectory;
use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdFixAttributesCommand extends Command
{
    /**
     * @var ActiveDirectory
     */
    private $activeDirectory;

    /**
     * @var BisDir
     */
    private $bisDir;

    /**
     * @var BisPersonView
     */
    private $bisPersonView;

    /**
     * AdFixAttributesCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory Active directory Service
     *
     * @param BisPersonView   $bisPersonView
     * @param BisDir          $bisDir
     */
    public function __construct(ActiveDirectory $activeDirectory, BisPersonView $bisPersonView, BisDir $bisDir)
    {
        $this->activeDirectory = $activeDirectory;
        $this->bisDir = $bisDir;
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
        $this->setName('ad:fix:attributes')
            ->setDescription('Fix attributes')
            ->addArgument('email', InputArgument::OPTIONAL, 'User email [@enabel.be]?');
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Account',
            'Old value',
            'New value',
            'Status',
        ]);
        $data = [];

        if (empty($input->getArgument('email'))) {
            foreach ($this->activeDirectory->getAllUsers('cn') as $adAccount) {
                $oldValue = $adAccount->getProxyAddresses();
                $newValue = $this->fixProxyAddresses($oldValue);
                $adAccount->setProxyAddresses($newValue);
                $status = $adAccount->save();
                $data[] = $this->fixAttributesResultAsLog($adAccount, $oldValue, $newValue, $status);
            }
        } else {
            $adAccount = $this->activeDirectory->getUser($input->getArgument('email'));
            $oldValue = $adAccount->getProxyAddresses();
            $newValue = $this->fixProxyAddresses($oldValue);
            $adAccount->setProxyAddresses($newValue);
            $status = $adAccount->save();
            $data[] = $this->fixAttributesResultAsLog($adAccount, $oldValue, $newValue, $status);
        }

        $table->setRows($data);
        $table->render();
    }

    private function fixAttributesResultAsLog(User $adAccount, $oldValue, $newValue, $status)
    {
        if ($status) {
            $data = [
                'account' => $adAccount->getEmail(),
                'old_value' => json_encode($oldValue),
                'new_value' => json_encode($newValue),
                'status' => "<info>\xF0\x9F\x97\xB8</info>",
            ];
        } else {
            $data = [
                'account' => $adAccount->getEmail(),
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'status' => "<fg=red>\xF0\x9F\x97\xB4</>",
            ];
        }

        return $data;
    }

    private function fixProxyAddresses($oldProxyAddresses)
    {
        $newProxyAddresses = [];
        if (is_array($oldProxyAddresses)) {
            foreach ($oldProxyAddresses as $proxyAddress) {
                if (strpos($proxyAddress, '@btcctb.org') === false) {
                    $newProxyAddresses[] = $proxyAddress;
                }
            }
        }

        return $newProxyAddresses;
    }
}
