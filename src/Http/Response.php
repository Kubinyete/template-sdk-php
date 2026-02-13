<?php

namespace Teamipag\Sdk\Http;

use Teamipag\Sdk\Util\ArrayUtil;
use Psr\Http\Message\ResponseInterface;
use Teamipag\Sdk\IO\SerializerInterface;

class Response
{
    protected ResponseInterface $response;
    protected ?SerializerInterface $serializer;

    /** @var array<array-key,mixed>|null */
    protected ?array $data;

    protected function __construct(?SerializerInterface $serializer, ResponseInterface $response)
    {
        $this->serializer = $serializer;
        $this->response = $response;
        $this->data = null;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returns the response body parsed as an array, if a serializer is set. Otherwise, returns null.
     *
     * @return array<array-key,mixed>|null
     */
    public function getParsed(): ?array
    {
        return $this->data ??
            ($this->data = $this->serializer ? $this->serializer->unserialize($this->getBody()) : null);
    }

    public function getParsedPath(string $dotNotation, mixed $default = null): mixed
    {
        return ArrayUtil::get($dotNotation, $this->getParsed() ?? [], $default);
    }

    public function getBody(): string
    {
        $stream = $this->response->getBody();
        $stream->rewind();
        return $stream->getContents();
    }

    public function setSerializer(?SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getStatusMessage(): string
    {
        return $this->response->getReasonPhrase();
    }

    //

    public static function from(ResponseInterface $response): static
    {
        //@phpstan-ignore-next-line
        return new static(null, $response);
    }
}
