<?php

namespace Teamipag\Sdk\Util;

abstract class ClassUtil
{
    /**
     * Returns the base name of a class, stripping away the namespace. For example, given "Teamipag\Sdk\Util\ClassUtil", it will return "ClassUtil".
     *
     * @param string $class
     * @return string
     */
    public static function basename(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }
}
