# Template SDK/Webservice Project

## What?
This project provides a basic quick-start template for creating Webservice clients.

## Why?
Instead of repeating the same things over and over again for every webservice client that your application needs, this template provides a quick and easy solution for
building and mapping endspoints to your client, providing a structured approach for endpoint representation, environment mapping and error handling.

## How?
To get started, just extend the base `Core/Client` and create your first endpoint, following the example below:

```php
# OpenMeteoClient.php
<?php

namespace Kubinyete\ExampleSdkPhp;

use Kubinyete\ExampleSdkPhp\Endpoint\ForecastEndpoint;
use Kubinyete\ExampleSdkPhp\Exception\OpenMeteoException;
use Kubinyete\ExampleSdkPhp\Client;
use Kubinyete\ExampleSdkPhp\Exception\HttpException;
use Kubinyete\ExampleSdkPhp\Http\Client\GuzzleHttpClient;
use Kubinyete\ExampleSdkPhp\Http\Response;
use Kubinyete\ExampleSdkPhp\IO\JsonSerializer;

class OpenMeteoClient extends Client
{
    public function __construct(int $version = 1)
    {
        parent::__construct(
            new OpenMeteoEnvironment($version),
            new GuzzleHttpClient(),
            new JsonSerializer()
        );
    }

    public function forecast(): ForecastEndpoint
    {
        return ForecastEndpoint::create($this, $this);
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
    // Translating Client/Server errors
    protected function exceptionThrown(Throwable $e): void
    {
        if ($e instanceof HttpException && $e->getResponse()) {
            $this->responseReceived($e->getResponse());
        }

        throw $e;
    }
}
```
```php
# ForecastEndpoint.php
<?php

namespace Kubinyete\ExampleSdkPhp\Endpoint;

use Kubinyete\ExampleSdkPhp\Model\Forecast;
use Kubinyete\ExampleSdkPhp\Model\ForecastSettings;
use Kubinyete\ExampleSdkPhp\Core\Endpoint;

class ForecastEndpoint extends Endpoint
{
    protected string $location = '/forecast';

    // @NOTE:
    // Using a more object-oriented approach using an Endpoint object.
    public function now(ForecastSettings $forecastSettings): Forecast
    {
        $response = $this->get($forecastSettings->jsonSerialize());
        return Forecast::parse($response->getParsed());
    }
}
```
```php
# usage.php
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Kubinyete\ExampleSdkPhp\Model\ForecastSettings;
use Kubinyete\ExampleSdkPhp\OpenMeteoClient;
use Kubinyete\ExampleSdkPhp\Exception\HttpClientException;

$client = new OpenMeteoClient();

try {
    $forecast = $client->forecast()->now(new ForecastSettings(52.52, 13.41, true));
    var_dump($forecast);
} catch (HttpClientException $e) {
    echo "Bad request!" . PHP_EOL;
    echo "Message: {$e->getMessage()}" . PHP_EOL;
    echo "Response: {$e->getResponse()->getBody()}" . PHP_EOL;
}

```
