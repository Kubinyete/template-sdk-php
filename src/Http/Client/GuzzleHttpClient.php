<?php

namespace Teamipag\Sdk\Http\Client;

use RuntimeException;
use GuzzleHttp\Client;
use Teamipag\Sdk\Http\Response;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use Teamipag\Sdk\Exception\HttpClientException;
use Teamipag\Sdk\Exception\HttpServerException;
use Teamipag\Sdk\Exception\HttpTransferException;

class GuzzleHttpClient extends BaseHttpClient
{
    private const DEFAULT_USER_AGENT = 'Template SDK for PHP';
    private const DEFAULT_CONFIG = [
        'allow_redirects' => false,
        'timeout' => 60.00,
        'connect_timeout' => 10.00,
        'http_errors' => true,
        'headers' => [
            'User-Agent' => self::DEFAULT_USER_AGENT
        ],
    ];

    protected Client $client;

    /**
     * @param array<array-key,mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->client = new Client(array_merge(self::DEFAULT_CONFIG, $config));
    }

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
    public function request(string $method, string $url, ?string $body, array $query = [], array $header = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $url, [
                'headers' => $header,
                'query' => $query,
                'body' => $body,
            ]);
        } catch (ConnectException $e) {
            // Networking error
            throw new HttpTransferException("An networking error ocurred while trying to connect to `$url`", $e->getCode(), $e);
        } catch (ServerException $e) {
            // Server-side error 5xx
            throw new HttpServerException(
                "An server-side error ocurred with status {$e->getResponse()->getStatusCode()} from `$url`",
                $e->getCode(),
                $e,
                // If this fails, an RuntimeException will be thrown
                Response::from($e->getResponse()),
            );
        } catch (ClientException $e) {
            // Client-side error 4xx
            throw new HttpClientException(
                "An client-side error ocurred with status {$e->getResponse()->getStatusCode()} from `$url`",
                $e->getCode(),
                $e,
                // If this fails, an RuntimeException will be thrown
                Response::from($e->getResponse()),
            );
        } catch (RuntimeException $e) {
            // Stream error
            throw $e;
        }
    }
}
