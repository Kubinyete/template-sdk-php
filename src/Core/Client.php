<?php

namespace Kubinyete\TemplateSdkPhp\Core;

use JsonSerializable;
use Kubinyete\TemplateSdkPhp\Exception\HttpException;
use Kubinyete\TemplateSdkPhp\Http\Client\BaseHttpClient;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\IO\SerializerInterface;
use Kubinyete\TemplateSdkPhp\Path\CompositePathInterface;
use Throwable;

abstract class Client implements CompositePathInterface
{
    protected Environment $environment;
    protected BaseHttpClient $httpClient;
    protected ?SerializerInterface $defaultSerializer;

    public function __construct(Environment $environment, BaseHttpClient $httpClient, ?SerializerInterface $defaultSerializer = null)
    {
        $this->environment = $environment;
        $this->httpClient = $httpClient;
        $this->defaultSerializer = $defaultSerializer;
    }

    //

    protected function serialize($body): ?string
    {
        $serializer ??= $this->defaultSerializer;

        if ($body instanceof JsonSerializable) {
            $body = $body->jsonSerialize();
        }

        if (is_array($body) && $serializer) {
            $body = $serializer->serialize($body);
        }

        if (is_object($body) && $serializer) {
            $body = $serializer->serialize(get_object_vars($body));
        }

        return $body;
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

    protected function get(string $path, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), null, $query, $header);
    }

    protected function post(string $path, $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    protected function put(string $path, $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    protected function patch(string $path, $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    protected function delete(string $path, $body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), $body, $query, $header);
    }

    protected function head(string $path, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $this->joinPath($path), null, $query, $header);
    }

    public function request(string $method, string $url, $body, array $query = [], array $header = [], ?SerializerInterface $serializer = null): Response
    {
        $serializer ??= $this->defaultSerializer;

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
                $this->serialize($body),
                $query,
                $header
            );

            $response = Response::from($response);
            $response->setSerializer($serializer);

            return $this->responseReceived($response) ?? $response;
        } catch (HttpException $e) {
            $response = $e->getResponse();

            if ($response) {
                $response->setSerializer($serializer);
            }

            $this->exceptionThrown($e);
        } catch (Throwable $e) {
            $this->exceptionThrown($e);
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
}
