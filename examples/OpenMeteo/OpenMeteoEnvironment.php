<?php

namespace Kubinyete\OpenMeteoSdkPhp;

use Kubinyete\TemplateSdkPhp\Core\Environment;

class OpenMeteoEnvironment extends Environment
{
    private const SERVICE_URL = 'https://api.open-meteo.com';

    public function __construct(int $version = 1)
    {
        parent::__construct(self::SERVICE_URL);
        $this->url = $this->joinPath("/v{$version}");
    }
}
