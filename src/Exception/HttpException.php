<?php

namespace Kubinyete\TemplateSdkPhp\Exception;

use Kubinyete\TemplateSdkPhp\Http\Response;
use Throwable;

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
        ?int $statusCode = null,
        ?string $statusMessage = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
        $this->statusCode = $statusCode;
        $this->statusMessage = $statusMessage;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }
}
