<?php

namespace Kubinyete\TemplateSdkPhp\Http;

use Psr\Http\Message\ResponseInterface;
use Kubinyete\TemplateSdkPhp\Util\ArrayUtil;
use Kubinyete\TemplateSdkPhp\IO\JsonSerializer;
use Kubinyete\TemplateSdkPhp\IO\MutatorInterface;
use Kubinyete\TemplateSdkPhp\IO\SerializerInterface;

class Response
{
    protected ResponseInterface $response;
    protected ?SerializerInterface $serializer;
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

    public function getParsed(): ?array
    {
        return $this->data ??
            ($this->data = $this->serializer ? $this->serializer->unserialize($this->getBody()) : null);
    }

    public function getParsedPath(string $dotNotation, $default = null)
    {
        return ArrayUtil::get($dotNotation, $this->getParsed(), $default);
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

    public static function from(ResponseInterface $response): self
    {
        return new static(null, $response);
    }
}
