<?php

namespace Kubinyete\TemplateSdkPhp\Model\Schema;

use Kubinyete\TemplateSdkPhp\Model\Schema\Exception\SchemaAttributeParseException;

class SchemaIntegerAttribute extends SchemaAttribute
{
    public function parseContextual($value)
    {
        if (is_integer($value)) {
            return $value;
        }

        throw new SchemaAttributeParseException($this, "Provided value '$value' is not an integer");
    }
}
