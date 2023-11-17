<?php

namespace Kubinyete\TemplateSdkPhp\Exception;

use Throwable;
use Kubinyete\TemplateSdkPhp\Http\Response;

abstract class HttpException extends BaseException
{
    protected ?Response $response;
    protected ?int $statusCode;
    protected ?string $statusMessage;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        ?Response $response = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getStatusCode(): ?int
    {
        return $this->response?->getStatusCode();
    }

    public function getStatusMessage(): ?string
    {
        return $this->response?->getStatusMessage();
    }
}
