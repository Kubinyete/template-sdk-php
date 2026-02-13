<?php

namespace Teamipag\Sdk\IO;

use InvalidArgumentException;

class FormUrlencodedSerializer implements SerializerInterface
{
    public function __construct() {}

    public function serialize(array $data): string
    {
        $output = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $key = $key . '[]';

                foreach ($value as $v) {
                    $output .= $key . '=' . urlencode($this->normalize($v)) . '&';
                }
            } else {
                $output .= $key . '=' . urlencode($this->normalize($value)) . '&';
            }
        }

        return rtrim($output, '&');
    }

    public function unserialize(string $data): array
    {
        $pieces = explode('&', $data);
        $output = [];

        foreach ($pieces as $piece) {
            [$key, $value] = explode('=', $piece, 2);
            $output[$key] = urldecode($value);
        }

        return $output;
    }

    public function getContentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }

    private function normalize(mixed $value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        throw new InvalidArgumentException("Unsupported value type for form-urlencoded serialization: " . get_debug_type($value));
    }
}
