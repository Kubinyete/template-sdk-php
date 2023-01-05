<?php

namespace Kubinyete\TemplateSdkPhp\Core;

use JsonSerializable;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\IO\SerializerInterface;
use Kubinyete\TemplateSdkPhp\Path\CompositePathInterface;
use Kubinyete\TemplateSdkPhp\Util\PathUtil;

class Endpoint implements CompositePathInterface
{
    protected Client $client;
    protected CompositePathInterface $parent;
    protected ?SerializerInterface $serializer;
    protected string $location;

    public function __construct(Client $client, CompositePathInterface $parent, string $location = '', ?SerializerInterface $serializer = null)
    {
        $this->client = $client;
        $this->parent = $parent;
        $this->location = $location;
        $this->serializer = $serializer;
    }

    //

    public function get(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
    }

    public function post($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    public function put($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    public function patch($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    public function delete(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
    }

    public function head(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
    }

    public function with(string $id): Endpoint
    {
        return $this->endpoint($id, $this->serializer);
    }

    //

    protected function request(string $method, $body, array $query = [], array $header = []): Response
    {
        return $this->client->request(
            strtoupper($method),
            $this->getPath(),
            $body,
            $query,
            $header,
            $this->serializer
        );
    }

    protected function endpoint(string $name = '', ?SerializerInterface $serializer = null): Endpoint
    {
        return new Endpoint($this->client, $this, $name, $serializer);
    }

    //

    public function getParent(): ?CompositePathInterface
    {
        return $this->parent;
    }

    public function setParent(?CompositePathInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function getPath(): string
    {
        return $this->getParent()->joinPath($this->location);
    }

    public function joinPath(string $relative): string
    {
        return implode(PathUtil::PATH_SEPARATOR, [$this->getPath(), $relative]);
    }
}
