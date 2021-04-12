<?php

namespace Edw\Service;

use Psr\Log\LoggerInterface;

interface ApiInterface
{
    public function __construct(string $email, string $password, string $baseUrl, LoggerInterface $logger);

    public function getToken();

    public function getBaseUrl();
}
