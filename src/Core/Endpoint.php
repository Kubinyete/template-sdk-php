<?php

namespace Teamipag\Sdk\Core;

use JsonSerializable;
use Teamipag\Sdk\Http\Response;
use Teamipag\Sdk\IO\SerializerInterface;
use Teamipag\Sdk\Path\CompositePathInterface;
use Teamipag\Sdk\Util\PathUtil;

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

    protected function get(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    protected function post($body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    protected function put($body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    protected function patch($body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    protected function delete(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    protected function head(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    //

    protected function request(string $method, $body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->client->request(
            strtoupper($method),
            $relativeUrl ? $this->joinPath($relativeUrl) : $this->getPath(),
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
        return implode(PathUtil::PATH_SEPARATOR, [$this->getPath(), ltrim($relative, PathUtil::PATH_SEPARATOR)]);
    }

    //

    public static function create(Client $client, CompositePathInterface $parent, ?string $location = null, ?SerializerInterface $serializer = null)
    {
        return new static($client, $parent, $location, $serializer);
    }
}
