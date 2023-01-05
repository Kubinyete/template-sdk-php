<?php

namespace Kubinyete\OpenMeteoSdkPhp;

use Kubinyete\OpenMeteoSdkPhp\Endpoint\ForecastEndpoint;
use Kubinyete\OpenMeteoSdkPhp\Exception\OpenMeteoException;
use Kubinyete\TemplateSdkPhp\Core\Client;
use Kubinyete\TemplateSdkPhp\Core\Endpoint;
use Kubinyete\OpenMeteoSdkPhp\Model\Forecast;
use Kubinyete\OpenMeteoSdkPhp\Model\ForecastSettings;
use Kubinyete\TemplateSdkPhp\Exception\HttpClientException;
use Kubinyete\TemplateSdkPhp\Exception\HttpException;
use Kubinyete\TemplateSdkPhp\Http\Client\GuzzleHttpClient;
use Kubinyete\TemplateSdkPhp\Http\Response;
use Kubinyete\TemplateSdkPhp\IO\JsonSerializer;
use Throwable;

class OpenMeteoClient extends Client
{
    private const DEFAULT_USER_AGENT = 'OpenMeteoClient SDK';

    public function __construct(int $version = 1)
    {
        parent::__construct(
            new OpenMeteoEnvironment($version),
            new GuzzleHttpClient(
                ['headers' => [
                    'User-Agent' => self::DEFAULT_USER_AGENT
                ]]
            ),
            new JsonSerializer()
        );
    }

    // @NOTE:
    // This is exposing our endpoint object, on a real
    // project you should wrap everything using models
    // check out our getCurrentForecast implementation below.
    public function forecast(): ForecastEndpoint
    {
        return ForecastEndpoint::create($this, $this, __FUNCTION__);
    }

    //

    // @NOTE:
    // Dealing with errors on 200 status codes
    protected function responseReceived(Response $response): Response
    {
        if ($response->getParsedPath('error')) {
            throw new OpenMeteoException((string)$response->getParsedPath('reason'), 0, null, $response);
        }

        return $response;
    }

    // @NOTE:
    // Translating errors
    protected function exceptionThrown(Throwable $e): void
    {
        if ($e instanceof HttpException && $e->getResponse()) {
            $this->responseReceived($e->getResponse());
        }

        throw $e;
    }
}
