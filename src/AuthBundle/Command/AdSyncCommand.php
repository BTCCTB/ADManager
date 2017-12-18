<?php

namespace AuthBundle\Command;

use BisBundle\Entity\BisPersonView;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AdSyncCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('crontasks:ad:sync')->setDescription('Synchronise the AD with GO4HR data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set no limit time & memory
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        // Get the container
        $container = $this->getContainer();
        // Get the AD service
        $ad = $container->get('auth.active_directory');
        // Get em for BIS
        $bis = $container->get('doctrine.orm.bis_entity_manager');
        // Get BisPersonView Repository
        $bisPersonView = $bis->getRepository('BisBundle:BisPersonView');

        // Get active users in GO4HR
        $bisUsers = $bisPersonView->findBy(['perCountryWorkplace' => 'BDI']);
        var_dump($ad->createCountryOu('MRT'));exit();
        foreach ($bisUsers as $bisUser) {
            /**
             * @var BisPersonView $bisUser
             */
            $ou = $ad->checkOuExistByName($bisUser->getCountryWorkplace());
            if ($ou !== false) {
                var_dump($ou);exit();
            }

            if (!empty($bisUser->getEmail()) && $ad->checkUserExistByUsername($bisUser->getUsername()) === false) {
                $bisUser->setGeneratedPassword($ad->generatePassword());
                $output->write($bisUser->getEmail() . ' | ' . $bisUser->getDomainAccount() . ' | ' . $bisUser->getLogin() . ' | ' . $bisUser->getGeneratedPassword(), true);
                $message = (new Swift_Message('Enabel credentials'))
                    ->setFrom('ict.helpdesk@btcctb.org')
                    ->setTo('ict.helpdesk@btcctb.org')
                    ->setBody(
                        $container->get('templating')->render(
                            '@Auth/Emails/credentials.html.twig',
                            array(
                                'user' => $bisUser,
                            )
                        ),
                        'text/html'
                    )
                    ->addPart(
                        $container->get('templating')->render(
                            '@Auth/Emails/credentials.text.twig',
                            array(
                                'user' => $bisUser,
                            )
                        ),
                        'text/plain'
                    );
//                $container->get('mailer')->send($message);
            }

        }

    }

}
