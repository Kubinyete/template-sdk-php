<?php

namespace Teamipag\Sdk\Core;

use Teamipag\Sdk\Http\Response;
use Teamipag\Sdk\Util\PathUtil;
use Teamipag\Sdk\IO\SerializerInterface;
use Teamipag\Sdk\Path\CompositePathInterface;

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
        $this->location = $this->location ?? $location ?? '';
        $this->serializer = $serializer;
    }

    //

    /**
     * Performs a GET request to the endpoint.
     *
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function get(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    /**
     * Performs a POST request to the endpoint.
     *
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function post(mixed $body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    /**
     * Performs a PUT request to the endpoint.
     *
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function put(mixed $body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    /**
     * Performs a PATCH request to the endpoint.
     *
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function patch(mixed $body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, $body, $query, $header, $relativeUrl);
    }

    /**
     * Performs a DELETE request to the endpoint.
     *
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function delete(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    /**
     * Performs a HEAD request to the endpoint.
     *
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function head(array $query = [], array $header = [], ?string $relativeUrl = null): Response
    {
        return $this->request(__FUNCTION__, null, $query, $header, $relativeUrl);
    }

    //

    /**
     * Performs a request to the endpoint using the given method, body, query and header. The URL is built by joining the endpoint path with the given relative URL (if any).
     *
     * @param string $method
     * @param mixed $body
     * @param array<array-key,mixed> $query
     * @param array<array-key,mixed> $header
     * @param string|null $relativeUrl
     * @return Response
     */
    protected function request(string $method, mixed $body, array $query = [], array $header = [], ?string $relativeUrl = null): Response
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
        if (!is_null($parent)) {
            $this->parent = $parent;
        }
    }

    public function getPath(): string
    {
        return $this->getParent()?->joinPath($this->location) ?? '';
    }

    public function joinPath(string $relative): string
    {
        return implode(PathUtil::PATH_SEPARATOR, [$this->getPath(), ltrim($relative, PathUtil::PATH_SEPARATOR)]);
    }

    //

    public static function create(Client $client, CompositePathInterface $parent, ?string $location = null, ?SerializerInterface $serializer = null): static
    {
        // @phpstan-ignore-next-line
        return new static($client, $parent, $location, $serializer);
    }
}
