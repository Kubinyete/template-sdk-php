<?php

namespace Kubinyete\OpenMeteoSdkPhp\Endpoint;

use Kubinyete\OpenMeteoSdkPhp\Model\Forecast;
use Kubinyete\OpenMeteoSdkPhp\Model\ForecastSettings;
use Kubinyete\TemplateSdkPhp\Core\Endpoint;

class ForecastEndpoint extends Endpoint
{
    protected string $location = 'forecast';

    // @NOTE:
    // Using a more object-oriented approach using an Endpoint object.
    public function now(ForecastSettings $forecastSettings): Forecast
    {
        $response = $this->get($forecastSettings->jsonSerialize());
        return Forecast::parse($response->getParsed());
    }
}
