<?php

namespace Kubinyete\TemplateSdkPhp\Model\Schema;

use DateTimeInterface;
use Kubinyete\TemplateSdkPhp\Model\Model;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaDateAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaStringAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaIntegerAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaRelationAttribute;

final class SchemaBuilder
{
    protected Schema $target;

    public function __construct(Schema $target)
    {
        $this->target = $target;
    }

    public function any(string $attribute): SchemaAttribute
    {
        return $this->target->any($attribute);
    }

    public function int(string $attribute): SchemaIntegerAttribute
    {
        return $this->target->int($attribute);
    }

    public function string(string $attribute): SchemaStringAttribute
    {
        return $this->target->string($attribute);
    }

    public function date(string $attribute, string $format = DateTimeInterface::RFC3339): SchemaDateAttribute
    {
        return $this->target->date($attribute)->format($format);
    }

    public function bool(string $attribute): SchemaBoolAttribute
    {
        return $this->target->bool($attribute);
    }

    public function enum(string $attribute, array $values): SchemaEnumAttribute
    {
        return $this->target->enum($attribute, $values);
    }

    public function float(string $attribute): SchemaFloatAttribute
    {
        return $this->target->float($attribute);
    }

    public function has(string $attribute, string $class = Model::class): SchemaRelationAttribute
    {
        return $this->target->has($attribute, $class);
    }

    public function hasMany(string $attribute, string $class = Model::class): SchemaRelationAttribute
    {
        return $this->target->hasMany($attribute, $class)->many();
    }

    //

    public function build(): Schema
    {
        return $this->target;
    }

    //

    public static function from(Schema $target): self
    {
        return new self($target);
    }
}
