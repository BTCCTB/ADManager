<?php

namespace AuthBundle\Service;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class ActiveDirectoryNotification
 *
 * @package AuthBundle\Service
 * @author  Damien Lagae <damienlagae@gmail.com>
 */
class ActiveDirectoryNotification
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $engine;
    /**
     * @var string
     */
    private $fromAddress;

    private $toAddress;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $engine, string $fromAddress, $toAddress)
    {
        $this->mailer = $mailer;
        $this->engine = $engine;
        $this->fromAddress = $fromAddress;
        $this->toAddress = $toAddress;
    }

    /**
     * Create email with users creation
     *
     * @param ActiveDirectoryResponse[] $activeDirectoryResponses
     *
     * @throws \RuntimeException
     */
    public function notifyCreation(array $activeDirectoryResponses)
    {
        $users = [];

        foreach ($activeDirectoryResponses as $activeDirectoryResponse) {
            if ($activeDirectoryResponse->getType() === ActiveDirectoryResponseType::CREATE &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::NOTHING_TO_DO) {
                $users[] = array_merge(
                    $activeDirectoryResponse->getData(),
                    [
                        'log' => $activeDirectoryResponse->getMessage(),
                    ]
                );
            }
        }

        if (!empty($users)) {
            $subject = 'User creation in AD';
            $message = \Swift_Message::newInstance();
            $message->setSubject($subject)
                ->setFrom($this->fromAddress)
                ->setTo($this->toAddress)
                ->setBody(
                    $this->engine->render(
                        '@Auth/Emails/notifyCreation.html.twig',
                        [
                            'users' => $users,
                            'subject' => $subject,
                        ]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->engine->render(
                        '@Auth/Emails/notifyCreation.text.twig',
                        [
                            'users' => $users,
                            'subject' => $subject,
                        ]
                    ),
                    'text/plain'
                );

            $this->mailer->send($message);
        }
    }

    /**
     * Create email with users move
     *
     * @param ActiveDirectoryResponse[] $activeDirectoryResponses
     *
     * @throws \RuntimeException
     */
    public function notifyMove(array $activeDirectoryResponses)
    {
        $users = [];

        foreach ($activeDirectoryResponses as $activeDirectoryResponse) {
            if ($activeDirectoryResponse->getType() === ActiveDirectoryResponseType::MOVE &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::NOTHING_TO_DO &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::ACTION_NEEDED) {
                $users[] = array_merge(
                    $activeDirectoryResponse->getData(),
                    [
                        'log' => $activeDirectoryResponse->getMessage(),
                    ]
                );
            }
        }

        if (!empty($users)) {
            $message = \Swift_Message::newInstance();
            $message->setSubject('User move in AD')
                ->setFrom($this->fromAddress)
                ->setTo($this->toAddress)
                ->setBody(
                    $this->engine->render(
                        '@Auth/Emails/notifyMove.html.twig',
                        [
                            'users' => $users,
                            'subject' => 'User move in AD',
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }

    public function notifyDisabled(array $activeDirectoryResponses)
    {
        $users = [];

        foreach ($activeDirectoryResponses as $activeDirectoryResponse) {
            if ($activeDirectoryResponse->getType() === ActiveDirectoryResponseType::DISABLE &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::NOTHING_TO_DO &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::ACTION_NEEDED) {
                $users[] = array_merge(
                    $activeDirectoryResponse->getData(),
                    [
                        'log' => $activeDirectoryResponse->getMessage(),
                    ]
                );
            }
        }

        if (!empty($users)) {
            $message = \Swift_Message::newInstance();
            $message->setSubject('User disabled in AD')
                ->setFrom($this->fromAddress)
                ->setTo($this->toAddress)
                ->setBody(
                    $this->engine->render(
                        '@Auth/Emails/notifyDisabled.html.twig',
                        [
                            'users' => $users,
                            'subject' => 'User disabled in AD',
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }

    public function notifyUpdate(array $activeDirectoryResponses)
    {
        $users = [];

        foreach ($activeDirectoryResponses as $activeDirectoryResponse) {
            if ($activeDirectoryResponse->getType() === ActiveDirectoryResponseType::UPDATE &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::NOTHING_TO_DO &&
                $activeDirectoryResponse->getStatus() !== ActiveDirectoryResponseStatus::ACTION_NEEDED) {
                $users[] = array_merge(
                    $activeDirectoryResponse->getData(),
                    [
                        'log' => $activeDirectoryResponse->getMessage(),
                    ]
                );
            }
        }

        if (!empty($users)) {
            $message = \Swift_Message::newInstance();
            $message->setSubject('User updated in AD')
                ->setFrom($this->fromAddress)
                ->setTo($this->toAddress)
                ->setBody(
                    $this->engine->render(
                        '@Auth/Emails/notifyUpdate.html.twig',
                        [
                            'users' => $users,
                            'subject' => 'User updated in AD',
                        ]
                    ),
                    'text/html'
                );

            $this->mailer->send($message);
        }
    }
}
