<?php

namespace Edw\Service\Go4hr;

use Edw\Service\ApiInterface;
use Edw\Service\EdwApi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class Contact
 *
 * @author Damien Lagae <damien.lagae@enabel.be>
 */
class Contact
{
    /**
     * @var ApiInterface
     */
    private $edw;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ApiInterface $edw, LoggerInterface $logger)
    {
        $this->edw = $edw;
        $this->logger = $logger;
    }

    /**
     * Get all contacts
     *
     * @return false|array Array of contact or false if failed
     * @throws DecodingExceptionInterface
     */
    public function getCollections()
    {
        $client = HttpClient::create();

        try {
            $this->logger->info('EdwApi - GO4HR\Contact: Get collections');
            $response = $client->request(
                'GET',
                $this->edw->getBaseUrl() . '/api/go4hr/contacts',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'auth_bearer' => $this->edw->getToken()
                ]
            );

            if ($response->getStatusCode() === 200) {
                $this->logger->debug('EdwApi - GO4HR\Contact: Get collection successful');
                $content = $response->toArray();
                if (is_array($content)) {
                    return $content;
                }
            } else {
                $this->logger->error(
                    'EdwApi - GO4HR\Contact: Get collection failed '.
                    '(' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'EdwApi - GO4HR\Contact: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'EdwApi - GO4HR\Contact: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'EdwApi - GO4HR\Contact: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'EdwApi - GO4HR\Contact: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return false;
    }
}
