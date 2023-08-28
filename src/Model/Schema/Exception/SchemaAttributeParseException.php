<?php

namespace Kubinyete\TemplateSdkPhp\Model\Schema\Exception;

use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaAttribute;

class SchemaAttributeParseException extends SchemaException
{
    public function __construct(SchemaAttribute $attribute, ?string $message = null)
    {
        $attributeName = $attribute->getAbsoluteName();
        $message ??= "Failed to parse attribute {$attribute->getName()}";
        parent::__construct("'{$attributeName}' {$message}");
    }
}
