<?php

namespace Kubinyete\OpenMeteoSdkPhp\Model;

use DomainException;
use Kubinyete\TemplateSdkPhp\Model\SerializableModelInterface;
use Throwable;

class ForecastSettings implements SerializableModelInterface
{
    // @NOTE:
    // This is just an example,
    // attributes should not be public/exposed to external modifications.
    public $latitude;
    public $longitude;
    public $current_weather;

    public function __construct(float $lat, float $long, bool $current_weather)
    {
        $this->latitude = $lat;
        $this->longitude = $long;
        $this->current_weather = $current_weather;
    }

    public static function tryParse(array $data): ?self
    {
        return self::parse($data);
    }

    public static function parse(array $data): self
    {
        throw new DomainException("This entity is not parseable");
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
