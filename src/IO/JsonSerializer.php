<?php

namespace Teamipag\Sdk\IO;

use InvalidArgumentException;
use Teamipag\Sdk\Exception\ParseException;

class JsonSerializer implements SerializerInterface
{
    protected int $flags;

    /** @var int<1, max> */
    protected int $depth;

    /**
     * @param integer $flags
     * @param int<1, max> $depth
     */
    public function __construct(int $flags = 0, int $depth = 512)
    {
        if ($depth < 1) {
            throw new InvalidArgumentException("Depth must be greater than 0.");
        }

        $this->flags = $flags;
        $this->depth = $depth;
    }

    public function serialize(array $data): string
    {
        $data = json_encode($data, $this->flags, $this->depth);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new ParseException("Serialization data is not JSON parseable.");
        }

        if (!is_string($data)) {
            throw new ParseException("Serialization data is not a JSON string.");
        }

        return $data;
    }

    public function unserialize(string $data): array
    {
        $json = json_decode($data, true, $this->depth, $this->flags);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new ParseException("Received data is not JSON parseable: $data.");
        }

        if (!is_array($json)) {
            throw new ParseException("Received data is not a JSON object or array: $data.");
        }

        return $json;
    }

    public function getContentType(): string
    {
        return 'application/json';
    }
}
