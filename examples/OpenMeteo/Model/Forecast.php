<?php

namespace Kubinyete\OpenMeteoSdkPhp\Model;

use Kubinyete\TemplateSdkPhp\Model\SerializableModelInterface;
use Throwable;

class Forecast implements SerializableModelInterface
{
    // @NOTE:
    // This is just an example,
    // attributes should not be public/exposed to external modifications.
    public $latitude;
    public $longitude;
    public $generationtime_ms;
    public $utc_offset_seconds;
    public $timezone;
    public $timezone_abbreviation;
    public $elevation;
    public $current_weather;

    public function __construct(array $data)
    {
        $this->latitude = $data['latitude'];
        $this->longitude = $data['longitude'];
        $this->generationtime_ms = $data['generationtime_ms'];
        $this->utc_offset_seconds = $data['utc_offset_seconds'];
        $this->timezone = $data['timezone'];
        $this->timezone_abbreviation = $data['timezone_abbreviation'];
        $this->elevation = $data['elevation'];
        $this->current_weather = $data['current_weather'];
    }

    public static function tryParse(array $data): ?self
    {
        try {
            return self::parse($data);
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function parse(array $data): self
    {
        return new static($data);
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
