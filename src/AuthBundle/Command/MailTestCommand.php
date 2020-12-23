<?php

namespace AuthBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailTestCommand extends Command
{
    private $mailer;
    private $twig;
    /**
     * @var string
     */
    private $fromAddress;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, string $fromAddress)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->fromAddress = $fromAddress;
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
            ->setDescription('Test mail.');
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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subject = 'Test mail from symfony';
        $message = (new \Swift_Message($subject))
            ->setFrom($this->fromAddress)
            ->setTo('damien.lagae@enabel.be')
            ->setBody(
                'test mail',
                'text/plain'
            );
        $this->mailer->send($message);

        return 0;
    }
}
