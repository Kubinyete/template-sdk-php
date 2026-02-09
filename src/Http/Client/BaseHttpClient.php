<?php

namespace Teamipag\Sdk\Http\Client;

use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Teamipag\Sdk\Exception\HttpClientException;
use Teamipag\Sdk\Exception\HttpServerException;
use Teamipag\Sdk\Exception\HttpTransferException;

abstract class BaseHttpClient
{
    /**
     * Uses the current http client wrapper to do a request.
     *
     * @throws RuntimeException
     * @throws HttpTransferException
     * @throws HttpClientException
     * @throws HttpServerException
     * 
     * @param string $method
     * @param string $url
     * @param string|null $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return ResponseInterface
     */
    public abstract function request(string $method, string $url, ?string $body, array $query = [], array $header = []): ResponseInterface;
}
