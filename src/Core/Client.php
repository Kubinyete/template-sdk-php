<?php

namespace Teamipag\Sdk\Core;

use Throwable;
use JsonSerializable;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Teamipag\Sdk\Http\Response;
use Teamipag\Sdk\IO\SerializerInterface;
use Teamipag\Sdk\Exception\HttpException;
use Teamipag\Sdk\Http\Client\BaseHttpClient;
use Teamipag\Sdk\Path\CompositePathInterface;

abstract class Client implements CompositePathInterface
{
    private static int $requestCounter = 0;

    protected Environment $environment;
    protected BaseHttpClient $httpClient;
    protected ?SerializerInterface $defaultSerializer;
    protected ?LoggerInterface $logger;

    public function __construct(Environment $environment, BaseHttpClient $httpClient, ?SerializerInterface $defaultSerializer = null, ?LoggerInterface $logger = null)
    {
        $this->environment = $environment;
        $this->httpClient = $httpClient;
        $this->defaultSerializer = $defaultSerializer;
        $this->logger = $logger ?? new NullLogger();
    }

    //

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    //

    /**
     * Serializes the given body using the provided serializer, or the default serializer if none is provided. If no serializer is available, returns the body as-is.
     *
     * @param mixed $body
     * @param SerializerInterface|null $serializer
     * @return string|null
     */
    protected function serialize(mixed $body, ?SerializerInterface $serializer = null): ?string
    {
        $serializer ??= $this->defaultSerializer;

        if (!$serializer) {
            return null;
        }

        if ($body instanceof JsonSerializable) {
            $body = $body->jsonSerialize();
        }

        if (is_array($body)) {
            $body = $serializer->serialize($body);
        }

        if (is_object($body)) {
            $body = $serializer->serialize(get_object_vars($body));
        }

        return is_string($body) ? $body : null;
    }

    //

    protected function responseReceived(Response $response): Response
    {
        return $response;
    }

    protected function exceptionThrown(Throwable $e): void
    {
        throw $e;
    }

    //

    /**
     * Performs an HTTP request to the given URL with the specified method, body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function get(string $path, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), null, $query, $header);
    }

    /**
     * Performs a POST request to the given URL with the specified body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function post(string $path, mixed $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    /**
     * Performs a PUT request to the given URL with the specified body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function put(string $path, mixed $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    /**
     * Performs a PATCH request to the given URL with the specified body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function patch(string $path, mixed $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    /**
     * Performs a DELETE request to the given URL with the specified body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function delete(string $path, mixed $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    /**
     * Performs a HEAD request to the given URL with the specified body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $path
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @return Response
     */
    protected function head(string $path, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), null, $query, $header);
    }

    /**
     * Performs a request to the given URL with the specified method, body, query parameters, and headers. It also handles serialization of the request body and deserialization of the response body using the provided serializers. If an HTTP error occurs, it throws an HttpException with the response details.
     *
     * @param string $method
     * @param string $url
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param SerializerInterface|null $inputSerializer
     * @param SerializerInterface|null $outputSerializer
     * @return Response
     */
    public function request(
        string $method,
        string $url,
        mixed $body,
        array $query = [],
        array $header = [],
        ?SerializerInterface $inputSerializer = null,
        ?SerializerInterface $outputSerializer = null
    ): Response {
        $requestId = $this->incrementRequestCounter();

        $outputSerializer ??= $this->defaultSerializer;
        $inputSerializer ??= $this->defaultSerializer;

        if ($inputSerializer) {
            $header['Content-Type'] = $header['Content-Type'] ?? $inputSerializer->getContentType();
        }

        if ($outputSerializer) {
            $header['Accept'] = $header['Accept'] ?? $outputSerializer->getContentType();
        }

        $this->logger?->debug("({$requestId}) {$method} {$url} : Sending request", ['body' => $body, 'query' => $query, 'header' => $header]);

        try {
            $response = $this->httpClient->request(
                $method,
                $url,
                // @NOTE:
                // We are not using our custom serializer from args
                // because it easier for an external user to send an alternative body encoded
                // with another serialization method (Ex: XML) as an string instead of
                // assuming that the response from the current endpoint is also expecting
                // to receive XML data.
                $this->serialize($body, $inputSerializer),
                $query,
                $header
            );

            $response = Response::from($response);
            $response->setSerializer($outputSerializer);

            $this->logger?->debug("({$requestId}) {$method} {$url} : Read successful", ['response' => $response->getBody()]);
            return $this->responseReceived($response);
        } catch (HttpException $e) {
            $response = $e->getResponse();
            $this->logger?->debug("({$requestId}) {$method} {$url} : Read failed with status code {$e->getStatusCode()} {$e->getStatusMessage()}", ['exception' => strval($e), 'response' => $response ? $response->getBody() : null]);

            if ($response) {
                $e->getResponse()?->setSerializer($outputSerializer);
            }

            throw $e;
        } catch (Throwable $e) {
            $this->logger?->debug("({$requestId}) {$method} {$url} : Read failed with unhandled exception", ['exception' => strval($e)]);
            throw $e;
        }
    }

    //

    public function getParent(): ?CompositePathInterface
    {
        return $this->environment->getParent();
    }

    public function setParent(?CompositePathInterface $parent): void
    {
        $this->environment->setParent($parent);
    }

    public function getPath(): string
    {
        return $this->environment->getPath();
    }

    public function joinPath(string $relative): string
    {
        if (filter_var($relative, FILTER_VALIDATE_URL)) {
            // If it's a valid URL, that means it's not a relative path, so
            // don't append it to our base.
            return $relative;
        }

        return $this->environment->joinPath($relative);
    }

    private function incrementRequestCounter(): int
    {
        return ++self::$requestCounter;
    }
}
