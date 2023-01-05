<?php

namespace Kubinyete\TemplateSdkPhp\Core;

use JsonSerializable;
use Kubinyete\TemplateSdkPhp\Exception\HttpException;
use Kubinyete\TemplateSdkPhp\Http\Client\BaseHttpClient;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\IO\SerializerInterface;
use Throwable;

abstract class Client
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

    protected function endpoint(string $name = '', ?SerializerInterface $serializer = null): Endpoint
    {
        return new Endpoint($this, $this->environment, $name, $serializer);
    }

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

    protected function responseReceived(Response $response): Response
    {
        return $response;
    }

    protected function exceptionThrown(Throwable $e): void
    {
        throw $e;
    }

    //

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
}
