<?php

namespace AuthBundle\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class SuccessFactorApi
 *
 * @package AuthBundle\Service
 *
 * @author  Damien Lagae <damien.lagae@enabel.be>
 */
class SuccessFactorApi
{
    private $authorizationToken;

    public function __construct(String $user, String $password, String $company)
    {
        $this->authorizationToken = 'Basic ' . base64_encode($user . '@' . $company . ':' . $password);
    }

    public function getUserPicture(String $userID)
    {
        $httpClient = HttpClient::create();
        try {
            $response = $httpClient->request(
                'GET',
                'https://api012.successfactors.eu/odata/v2/Photo(photoType=1,userId=\'' . $userID . '\')?$format=json',
                [
                    'headers' => [
                        'Authorization' => $this->authorizationToken,
                    ],
                ]
            );
            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                if (isset($content['d']['photo'])) {
                    return $content['d']['photo'];
                }
            }
        } catch (TransportExceptionInterface $e) {
        } catch (ClientExceptionInterface $e) {
        } catch (RedirectionExceptionInterface $e) {
        } catch (ServerExceptionInterface $e) {
        }

        return null;
    }
}
