<?php

namespace Kubinyete\TemplateSdkPhp\Http\Client;

use Psr\Http\Message\ResponseInterface;

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
     * @param array $query
     * @param array $header
     * @return ResponseInterface
     */
    public abstract function request(string $method, string $url, ?string $body, array $query = [], array $header = []): ResponseInterface;
}
