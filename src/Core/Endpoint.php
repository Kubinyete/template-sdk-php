<?php

namespace Kubinyete\TemplateSdkPhp\Core;

use JsonSerializable;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\IO\SerializerInterface;
use Kubinyete\TemplateSdkPhp\Path\CompositePathInterface;
use Kubinyete\TemplateSdkPhp\Util\PathUtil;

abstract class Endpoint implements CompositePathInterface
{
    protected Client $client;
    protected CompositePathInterface $parent;
    protected ?SerializerInterface $serializer;
    protected string $location;

    public function __construct(Client $client, CompositePathInterface $parent, ?string $location = null, ?SerializerInterface $serializer = null)
    {
        $this->client = $client;
        $this->parent = $parent;
        $this->location = $this->location ?? $location;
        $this->serializer = $serializer;
    }

    //

    protected function get(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
    }

    protected function post($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    protected function put($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    protected function patch($body, array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header);
    }

    protected function delete(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
    }

    protected function head(array $query = [], array $header = []): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header);
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

    //

    public static function create(Client $client, CompositePathInterface $parent, ?string $location = null, ?SerializerInterface $serializer = null)
    {
        return new static($client, $parent, $location, $serializer);
    }
}
