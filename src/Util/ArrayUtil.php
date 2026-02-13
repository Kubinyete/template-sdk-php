<?php

namespace Teamipag\Sdk\Util;

abstract class ArrayUtil
{
    public const ACCESS_SEPARATOR = '.';

    /**
     * Retrieves a value from a multi-dimensional array using a dot-separated path. For example, given the path "user.profile.name" and the array ["user" => ["profile" => ["name" => "John"]]], it will return "John". If the path does not exist, it returns the provided default value.
     *
     * @param string $path
     * @param array<array-key,mixed> $array
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $path, array $array, mixed $default = null): mixed
    {
        $splitPath = explode(self::ACCESS_SEPARATOR, $path);

        while (is_array($array) && ($key = array_shift($splitPath))) {
            if (array_key_exists($key, $array)) {
                $array = &$array[$key];
            } else {
                $array = null;
            }
        }

        return is_null($array) || !empty($splitPath) ? $default : $array;
    }


    /**
     * Sets a value in a multi-dimensional array using a dot-separated path. For example, given the path "user.profile.name", the array ["user" => ["profile" => []]], and the value "John", it will modify the array to ["user" => ["profile" => ["name" => "John"]]]. If any part of the path does not exist, it will create the necessary nested arrays.
     *
     * @param string $path
     * @param array<array-key,mixed> $array
     * @param mixed $value
     * @return void
     */
    public static function set(string $path, array &$array, mixed  $value = null): void
    {
        $splitPath = explode(self::ACCESS_SEPARATOR, $path);

        while ($key = array_shift($splitPath)) {
            if (!$splitPath) {
                $array[$key] = $value;
            } else {
                if (array_key_exists($key, $array) && is_array($array[$key])) {
                    $array = &$array[$key];
                } else {
                    $array[$key] = [];
                    $array = &$array[$key];
                }
            }
        }
    }
}
