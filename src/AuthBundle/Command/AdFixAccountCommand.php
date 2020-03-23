<?php

namespace AuthBundle\Command;

use App\Service\Account;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdFixAccountCommand extends Command
{
    /**
     * @var BisPersonView
     */
    private $bisPersonView;
    /**
     * @var Account
     */
    private $account;

    /**
     * AdFixAccountCommand constructor.
     *
     * @param Account       $account
     * @param BisPersonView $bisPersonView
     */
    public function __construct(Account $account, BisPersonView $bisPersonView)
    {
        $this->bisPersonView = $bisPersonView;
        $this->account = $account;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:account:fix')
            ->setDescription('Fix account')
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
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Employee ID',
            'Account',
            'Status',
        ]);
        $data = [];

        if (empty($input->getArgument('email'))) {
            foreach ($this->bisPersonView->getAllUsers() as $bisPerson) {
                $data[] = $this->upSertResultAsLog($this->account->upSertAccount($bisPerson), $bisPerson);
            }
        } else {
            $bisPerson = $this->bisPersonView
                ->getUser($input->getArgument('email'));
            $data[] = $this->upSertResultAsLog($this->account->upSertAccount($bisPerson), $bisPerson);
        }

        $table->setRows($data);
        $table->render();
    }

    /**
     * @param int                             $status
     * @param \BisBundle\Entity\BisPersonView $bisPerson
     *
     * @return array
     */
    private function upSertResultAsLog(int $status, \BisBundle\Entity\BisPersonView $bisPerson): array
    {
        switch ($status) {
            case 1:
                $data = [
                    'employeeID' => $bisPerson->getEmployeeId(),
                    'account' => $bisPerson->getEmail(),
                    'status' => "<info>\xF0\x9F\x97\xB8 created</info>",
                ];
                break;
            case 2:
                $data = [
                    'employeeID' => $bisPerson->getEmployeeId(),
                    'account' => $bisPerson->getEmail(),
                    'status' => "<fg=yellow>\xE2\x9A\xA1 already exist</>",
                ];
                break;
            case 0:
            default:
                $data = [
                    'employeeID' => $bisPerson->getEmployeeId(),
                    'account' => $bisPerson->getEmail(),
                    'status' => "<fg=red>\xE2\x9D\x8C missing email!</>",
                ];
                break;
        }

        return $data;
    }
}
