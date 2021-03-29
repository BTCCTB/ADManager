<?php

namespace AuthBundle\Service;

use Illuminate\Contracts\Queue\Job;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
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
    const ITEMS_PER_PAGES = 250;
    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * SuccessFactorApi constructor.
     */
    public function __construct(string $token, string $secret, string $baseUrl, LoggerInterface $logger)
    {
        $this->token = $token;
        $this->secret = $secret;
        $this->baseUrl = $baseUrl;
        $this->logger = $logger;
    }

    public function getUserPicture(String $userID)
    {
        $httpClient = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get picture for a employee');
            $response = $httpClient->request(
                'GET',
                $this->baseUrl . 'Photo(photoType=1,userId=\'' . $userID . '\')?$format=json',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                ]
            );
            if ($response->getStatusCode() === 200) {
                $this->logger->debug('SuccessFactorApi: Get picture for employee [' . $userID . ']');
                $content = $response->toArray();
                if (isset($content['d']['photo'])) {
                    return $content['d']['photo'];
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get picture for employee [' . $userID . '] '.
                    '(' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return null;
    }

    /**
     * Search users by name of id
     *
     * @param String $search Search query
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     */
    public function searchUsers(String $search): array
    {
        $users = [];
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Search employee');
            // Employees personal data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'PerPersonal',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$select' =>
                            'personIdExternal,lastName,firstName,preferredName,gender,'.
                            'personNav/emailNav/emailTypeNav/externalCode,personNav/emailNav/emailAddress,'.
                            'nativePreferredLangNav/externalCode,customString1Nav/externalCode',
                        '$expand' => 'personNav/emailNav/emailTypeNav,nativePreferredLangNav,customString1Nav',
                        '$filter' =>
                            "personNav/emailNav/emailAddress like '" . $search . "' ".
                            "or personIdExternal eq '" . $search . "'",
                        'fromDate' => '1990-01-01',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray(false);
                if (isset($data['d']) && 0 !== count($data['d']['results'])) {
                    $this->logger->debug(
                        'SuccessFactorApi: Search employee (' . count($data['d']['results']) . ')'
                    );

                    foreach ($data['d']['results'] as $employee) {
                        $job = $this->getUserJob($employee['personIdExternal']);
                        $email = $this->getUserMail($employee['personIdExternal']);
                        $users[] = [
                            'id' => $employee['personIdExternal'],
                            'lastname' => strtoupper($employee['lastName']),
                            'firstname' => $employee['firstName'],
                            'nickname' => $employee['preferredName'],
                            'gender' => $employee['gender'],
                            'startDate' => !empty($job['startDate']) ? $job['startDate'] : null,
                            'endDate' => !empty($job['endDate']) ? $job['endDate'] : null,
                            'active' => !empty($job['active']) ? $job['active'] : 0,
                            'emailEnabel' => $email,
                            'motherLanguage' =>
                                strtoupper(
                                    substr($employee['nativePreferredLangNav']['externalCode'], 0, 2)
                                ),
                            'preferredLanguage' =>
                            (
                                in_array(
                                    strtoupper(
                                        substr(
                                            $employee['customString1Nav']['externalCode'],
                                            0,
                                            2
                                        )
                                    ),
                                    [
                                        'EN', 'FR', 'NL'
                                    ]
                                ) ?
                                strtoupper(
                                    substr(
                                        $employee['customString1Nav']['externalCode'],
                                        0,
                                        2
                                    )
                                ) :
                                'EN'
                            ),
                            'phone' => $this->getUserPhone($employee['personIdExternal'], 3849),
                            'mobile' => $this->getUserPhone($employee['personIdExternal'], 3850),
                            'position' =>
                                !empty($job['position']) ?
                                    SuccessFactorApiHelper::positionFromCode($job['position']) :
                                    null,
                            'jobTitle' =>
                                !empty($job['jobTitle']) ? ucfirst(strtolower($job['jobTitle'])) : null,
                            'countryWorkplace' =>
                                !empty($job['countryWorkplace']) ? $job['countryWorkplace'] : null,
                            'managerId' =>
                                !empty($job['managerId']) ? $job['managerId'] : null,
                            'jobClass' => SuccessFactorApiHelper::jobTypeFromJobCode($job['jobCode'], $job['type']),
                        ];
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to search employee (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }
        return $users;
    }

    public function getUserPhone($userId, $phoneType = 3849)
    {
        $phone = null;
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get phones');
            // photo data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'PerPhone',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$filter' => 'phoneType eq ' . $phoneType . ' and personIdExternal eq ' . $userId,
                        '$select' => 'personIdExternal, phoneNumber, countryCodeNav/externalCode',
                        '$expand' => 'countryCodeNav',
                        'fromDate' => '1990-01-01',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray();
                if (isset($data['d'])) {
                    $this->logger->debug(
                        'SuccessFactorApi: Get phones (' . count($data['d']['results']) . ')'
                    );
                    if (isset($data['d']['results'])) {
                        foreach ($data['d']['results'] as $phoneRow) {
                            if (isset($phoneRow['personIdExternal'])) {
                                $this->logger->debug(
                                    'SuccessFactorApi: handle phone  for user ' . $phoneRow['personIdExternal']
                                );
                                $phone = SuccessFactorApiHelper::cleanPhoneNumber(
                                    $phoneRow['countryCodeNav']['externalCode'] . $phoneRow['phoneNumber']
                                );
                            }
                        }
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get phone (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return $phone;
    }

    public function getUserJob($userId)
    {
        $job = null;
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get jobs');
            // Employees personal data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'EmpJob',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$select' =>
                            'userId,employmentNav/startDate,employmentNav/endDate,emplStatusNav/externalCode,'.
                            'jobCodeNav/name,customString10,jobCodeNav/cust_string1,managerId,jobCode,position',
                        '$expand' => 'emplStatusNav, jobCodeNav,employeeClassNav,employmentNav',
                        '$filter' => 'userId eq ' . $userId,
                        'fromDate' => '1990-01-01',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray(false);
                if (isset($data['d'])) {
                    $this->logger->debug('SuccessFactorApi: Get jobs (' . count($data['d']['results']) . ')');
                    // Transform response as array of job
                    foreach ($data['d']['results'] as $empJob) {
                        $jobType = $empJob['jobCodeNav']['cust_string1'];
                        if (empty($jobType)) {
                            $jobType = SuccessFactorApiHelper::jobTypeFromJobCode($empJob['jobCode']);
                        }
                        $job = [
                            'startDate' =>
                                SuccessFactorApiHelper::SFDateToDateTime($empJob['employmentNav']['startDate']),
                            'endDate' =>
                                SuccessFactorApiHelper::SFDateToDateTime($empJob['employmentNav']['endDate']),
                            'active' => ('A' == $empJob['emplStatusNav']['externalCode']) ? true : false,
                            'jobTitle' => $empJob['jobCodeNav']['name'],
                            'countryWorkplace' => $empJob['customString10'],
                            'type' => $jobType,
                            'managerId' => (int) $empJob['managerId'],
                            'jobCode' => $empJob['jobCode'],
                            'position' => $empJob['position'],
                        ];
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get jobs (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return $job;
    }

    public function getUserMail($userId, $mailType = 3515)
    {
        $email = null;
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get emails');
            // photo data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'PerEmail',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$filter' => 'emailType eq ' . $mailType . ' and personIdExternal eq ' . $userId,
                        '$select' => 'personIdExternal, emailAddress',
                        'fromDate' => '1990-01-01',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray();
                if (isset($data['d'])) {
                    $this->logger->debug(
                        'SuccessFactorApi: Get emails (' . count($data['d']['results']) . ')'
                    );
                    if (isset($data['d']['results'])) {
                        foreach ($data['d']['results'] as $emailRow) {
                            if (isset($emailRow['personIdExternal'])) {
                                $this->logger->debug(
                                    'SuccessFactorApi: handle email  for user ' . $emailRow['personIdExternal']
                                );
                                $email = $emailRow['emailAddress'];
                            }
                        }
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get phone (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return $email;
    }

    public function getUserJobHistory($userId)
    {
        $job = null;
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get job history for userID: ' . $userId);
            // Employees personal data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'EmpJob',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$select' => 'userId,endDate,positionEntryDate,jobCode',
                        '$filter' =>
                            'userId eq ' . $userId .
                            " and endDate lt datetimeoffset'" . date("Y-m-d") . "T00:00:00Z'",
                        '$expand' => 'eventReasonNav',
                        'fromDate' => '1990-01-01',
                        '$orderby' => 'endDate',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray(false);
                if (isset($data['d'])) {
                    $this->logger->debug('SuccessFactorApi: Get job history (' . count($data['d']['results']) . ')');
                    // Transform response as array of job
                    foreach ($data['d']['results'] as $empJob) {
                        $job = [
                            'startDate' => SuccessFactorApiHelper::SFDateToDateFormat($empJob['positionEntryDate']),
                            'endDate' => SuccessFactorApiHelper::SFDateToDateFormat($empJob['endDate']),
                            'jobCode' => $empJob['jobCode']
                        ];
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get job history for userID: ' .
                    $userId . ' (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return $job;
    }

    /**
     * List of inactive user with end date.
     *
     * @param ProgressBar|null $progressBar
     *
     * @return null|array return array of inactive userId.
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     */
    public function getInactiveUsers(? ProgressBar $progressBar)
    {
        $users = null;
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get not active users');
            // Employees personal data
            $response = $client->request(
                'GET',
                $this->baseUrl . 'EmpJob',
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'query' => [
                        '$select' => 'userId,emplStatusNav/externalCode',
                        '$filter' => "emplStatusNav/externalCode eq 'T'",
                        '$expand' => 'emplStatusNav',
                        '$orderby' => 'userId',
                        'customPageSize' => self::ITEMS_PER_PAGES,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray(false);
                if (isset($data['d'])) {
                    $this->logger->debug(
                        'SuccessFactorApi: Get not active users (' . count($data['d']['results']) . ')'
                    );
                    while (false != $data) {
                        if (null !== $progressBar) {
                            $progressBar->advance(count($data['d']['results']));
                        }
                        // Transform response as array
                        $users = $this->resultToUserStatusArray($data, $users);
                        if (isset($data['d']['__next'])) {
                            $data = $this->getNextResult($data['d']['__next']);
                        } else {
                            $data = false;
                        }
                    }
                }
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get jobs (' . $response->getStatusCode() . ')'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }
        if (null !== $progressBar) {
            $progressBar->finish();
        }

        return $users;
    }

    public function resultToUserStatusArray(array $data, ? array $users)
    {
        if (isset($data['d']['results'])) {
            $this->logger->info('SuccessFactorApi: Transform empJob to job');
            foreach ($data['d']['results'] as $employee) {
                $users[(int) $employee['userId']] = [
                    'userId' => (int) $employee['userId'],
                    'active' => $employee['emplStatusNav']['externalCode']
                ];
            }
        }

        return $users;
    }


    /**
     * Handle OData pagination of SuccessFactorApi.
     *
     * @param string $nextUrl The next url to load
     *
     * @return array|bool The result response or null
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     */
    public function getNextResult($nextUrl)
    {
        $client = HttpClient::create();
        try {
            $this->logger->info('SuccessFactorApi: Get next page');
            // Get next page of results
            $response = $client->request(
                'GET',
                $nextUrl,
                [
                    'auth_basic' => [
                        $this->token,
                        $this->secret,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]
            );

            // Request successful
            if (200 === $response->getStatusCode()) {
                $data = $response->toArray(false);
                if (isset($data['d']['results']) && !empty($data['d']['results'])) {
                    $this->logger->info(
                        'SuccessFactorApi: Get next page (' . count($data['d']['results']) . ') [' . $nextUrl . ']'
                    );

                    return $data;
                }

                return false;
            } else {
                $this->logger->error(
                    'SuccessFactorApi: Unable to get next page (' . $response->getStatusCode() . ') [' . $nextUrl . ']'
                );
            }
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: transport exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: client exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (RedirectionExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: redirection exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        } catch (ServerExceptionInterface $e) {
            $this->logger->error(
                'SuccessFactorApi: server exception: [' . $e->getCode() . '] ' . $e->getMessage()
            );
        }

        return false;
    }
}
