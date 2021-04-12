<?php

namespace Edw\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class Edw
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class EdwApi implements ApiInterface
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $email, string $password, string $baseUrl, LoggerInterface $logger)
    {
        $this->email = $email;
        $this->password = $password;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }

    public function getToken()
    {
        $client = HttpClient::create();

        try {
            $this->logger->info('EdwApi: Get Token');
            $response = $client->request(
                'POST',
                $this->baseUrl . '/api/authentication_token',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode([
                        'email' => $this->email,
                        'password' => $this->password
                    ])
                ]
            );

            if ($response->getStatusCode() === 200) {
                $this->logger->debug('EdwApi: Authentication successful');
                $content = $response->toArray();
                if (isset($content['token'])) {
                    return $content['token'];
                }
            } else {
                $this->logger->error(
                    'EdwApi: Authentication failed '.
                    '(' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'EdwApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'EdwApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'EdwApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'EdwApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return false;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
