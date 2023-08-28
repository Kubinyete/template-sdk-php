<?php

namespace Kubinyete\TemplateSdkPhp\Util;

abstract class ClassUtil
{
    public static function basename(string $class): string
    {
        return basename(str_replace('\\', '/', $class));
    }
}
