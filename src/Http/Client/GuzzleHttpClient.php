<?php

namespace Kubinyete\TemplateSdkPhp\Http\Client;

use RuntimeException;
use GuzzleHttp\Client;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\Exception\HttpClientException;
use Kubinyete\TemplateSdkPhp\Exception\HttpServerException;
use Kubinyete\TemplateSdkPhp\Exception\HttpTransferException;

class GuzzleHttpClient extends BaseHttpClient
{
    private const DEFAULT_USER_AGENT = 'Template SDK for PHP';

    protected Client $client;

    public function __construct(array $config = [])
    {
        static $default = [
            'allow_redirects' => false,
            'timeout' => 60.00,
            'connect_timeout' => 10.00,
            'http_errors' => true,
            'headers' => [
                'User-Agent' => self::DEFAULT_USER_AGENT
            ],
        ];

        $this->client = new Client(array_merge($default, $config));
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
     * @param array $query
     * @param array $header
     * @return string|null
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
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getReasonPhrase()
            );
        } catch (ClientException $e) {
            // Client-side error 4xx
            throw new HttpClientException(
                "An client-side error ocurred with status {$e->getResponse()->getStatusCode()} from `$url`",
                $e->getCode(),
                $e,
                // If this fails, an RuntimeException will be thrown
                Response::from($e->getResponse()),
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getReasonPhrase()
            );
        } catch (RuntimeException $e) {
            // Stream error
            throw $e;
        }
    }
}
