<?php

namespace AuthBundle\Command;

use App\Service\Account;
use AuthBundle\Service\ActiveDirectory;
use BisBundle\Service\BisPersonView;
use BisBundle\Service\ViewLevelSf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdSyncLevelCommand extends Command
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
     * AdFixAccountCommand constructor.
     *
     * @param ActiveDirectory $activeDirectory
     * @param BisPersonView   $bisPersonView
     */
    public function __construct(ActiveDirectory $activeDirectory, BisPersonView $bisPersonView)
    {
        $this->activeDirectory = $activeDirectory;
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
        $this->setName('ad:sync:level')
            ->setDescription('Set department/service/cell from GO4HR to AD')
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
            'Employee ID',
            'Account',
            'Department',
            'Service',
            'Cell',
            'Manager',
            'Status',
        ]);
        $data = [];

        if (empty($input->getArgument('email'))) {
            foreach ($this->activeDirectory->getAllUsers('cn') as $adUser) {
                $bisPerson = null;
                if (!empty($adUser->getEmail())) {
                    $bisPerson = $this->bisPersonView->getUser($adUser->getEmail());
                }
                if (!empty($bisPerson)) {
                    $adUser->setCompany($bisPerson->getDepartment());
                    $adUser->setDepartment($bisPerson->getService());
                    $adUser->setFirstAttribute('division', $bisPerson->getCell());
                    $adUser->setPostOfficeBox($bisPerson->getManagerEmail());
                    $status = $adUser->save();
                    $data[] = [
                        'employeeID' => $adUser->getEmployeeId(),
                        'account' => $adUser->getEmail(),
                        'Department' => $adUser->getCompany(),
                        'Service' => $adUser->getDepartment(),
                        'Cell' => $adUser->getFirstAttribute('division'),
                        'Manager' => $adUser->getPostOfficeBox(),
                        'status' => ($status) ? "<info>\xF0\x9F\x97\xB8 updated</info>" : "<fg=yellow>\xE2\x9A\xA1 can't be updated !</>",
                    ];
                } else {
                    $data[] = [
                        'employeeID' => $adUser->getEmployeeId(),
                        'account' => $adUser->getEmail() . ' [' . $adUser->getDn() . ']',
                        'Department' => $adUser->getCompany(),
                        'Service' => $adUser->getDepartment(),
                        'Cell' => $adUser->getFirstAttribute('division'),
                        'Manager' => $adUser->getPostOfficeBox(),
                        'status' => "<fg=red>\xE2\x9D\x8C not found in BIS !</>",
                    ];
                }
            }
        } else {
            $adUser = $this->activeDirectory->getUser($input->getArgument('email'));
            if (!empty($adUser)) {
                $bisPerson = $this->bisPersonView->getUser($adUser->getEmail());
                if (!empty($bisPerson)) {
                    $adUser->setCompany($bisPerson->getDepartment());
                    $adUser->setDepartment($bisPerson->getService());
                    $adUser->setFirstAttribute('division', $bisPerson->getCell());
                    $adUser->setPostOfficeBox($bisPerson->getManagerEmail());
                    $status = $adUser->save();
                    $data[] = [
                        'employeeID' => $adUser->getEmployeeId(),
                        'account' => $adUser->getEmail(),
                        'Department' => $adUser->getCompany(),
                        'Service' => $adUser->getDepartment(),
                        'Cell' => $adUser->getFirstAttribute('division'),
                        'Manager' => $adUser->getPostOfficeBox(),
                        'status' => ($status) ? "<info>\xF0\x9F\x97\xB8 updated</info>" : "<fg=yellow>\xE2\x9A\xA1 can't be updated !</>",
                    ];
                } else {
                    $data[] = [
                        'employeeID' => $adUser->getEmployeeId(),
                        'account' => $adUser->getEmail(),
                        'Department' => $adUser->getCompany(),
                        'Service' => $adUser->getDepartment(),
                        'Cell' => $adUser->getFirstAttribute('division'),
                        'Manager' => $adUser->getPostOfficeBox(),
                        'status' => "<fg=red>\xE2\x9D\x8C not found in BIS !</>",
                    ];
                }
            } else {
                $data[] = [
                    'employeeID' => 'XXXXX',
                    'account' => $input->getArgument('email'),
                    'Department' => 'NC',
                    'Service' => 'NC',
                    'Cell' => 'NC',
                    'Manager' => $adUser->getPostOfficeBox(),
                    'status' => "<fg=red>\xE2\x9D\x8C not found in AD !</>",
                ];
            }
        }

        $table->setRows($data);
        $table->render();

        return 0;
    }
}
