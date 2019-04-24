<?php

namespace AuthBundle\Command;

use AuthBundle\Service\BisDir;
use BisBundle\Service\BisPersonView;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdCrontaskLdapCommand extends Command
{
    /**
     * @var BisPersonView
     */
    private $bisPersonView;

    /**
     * @var BisDir
     */
    private $bisDir;

    /**
     * AdCrontaskLdapCommand constructor.
     *
     * @param BisPersonView $bisPersonView
     * @param BisDir        $bisDir
     */
    public function __construct(BisPersonView $bisPersonView, BisDir $bisDir)
    {
        $this->bisPersonView = $bisPersonView;
        $this->bisDir = $bisDir;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('ad:crontask:ldap')
            ->setDescription('Cleanup entry/person who are no longer in GO4HR.');
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
     * @throws \RuntimeException
     * @throws \Adldap\AdldapException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputStyle = new OutputFormatterStyle('red', null, array('bold'));
        $output->getFormatter()->setStyle('warning', $outputStyle);

        $bisPersons = $this->bisPersonView->getActiveUserByEmail();

        $logs = $this->bisDir->disableFromBis($bisPersons);

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
