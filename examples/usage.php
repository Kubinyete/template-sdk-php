<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Kubinyete\OpenMeteoSdkPhp\Model\ForecastSettings;
use Kubinyete\OpenMeteoSdkPhp\OpenMeteoClient;
use Kubinyete\TemplateSdkPhp\Exception\HttpClientException;

$client = new OpenMeteoClient();

try {
    $forecast = $client->forecast()->now(new ForecastSettings(52.52, 13.41, true));

    var_dump($forecast);
} catch (HttpClientException $e) {
    echo "Bad request!" . PHP_EOL;
    echo "Message: {$e->getMessage()}" . PHP_EOL;
    echo "Response: {$e->getResponse()->getBody()}" . PHP_EOL;
}
