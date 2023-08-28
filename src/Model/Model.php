<?php

namespace Kubinyete\TemplateSdkPhp\Model;

use Kubinyete\TemplateSdkPhp\Model\Schema\Exception\MutatorAttributeException;
use Kubinyete\TemplateSdkPhp\Model\Schema\Exception\SchemaAttributeParseException;
use Kubinyete\TemplateSdkPhp\Model\Schema\Mutator;
use Kubinyete\TemplateSdkPhp\Model\Schema\MutatorContext;
use Kubinyete\TemplateSdkPhp\Model\Schema\Schema;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaBuilder;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaRelationAttribute;
use Kubinyete\TemplateSdkPhp\Model\SerializableModelInterface;
use Kubinyete\TemplateSdkPhp\Util\ClassUtil;
use UnexpectedValueException;

abstract class Model implements SerializableModelInterface
{
    private Schema $schema;
    private array $data;
    private array $relations;
    private string $name;
    private array $mutators;

    public final function __construct(array $data = [], array $relations = [], ?string $name = null)
    {
        $this->data = $data;
        $this->relations = $relations;
        $this->name = $name ?? ClassUtil::basename(static::class);
        $this->schema = new Schema($this->name);

        $this->schema($this->schema->builder());
        $this->schemaLoadDefaults();
    }

    //

    protected abstract function schema(SchemaBuilder $schema): Schema;

    //

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setSchemaName(string $name): self
    {
        $this->schema->setName($name);
        return $this;
    }

    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function set(string $attribute, $value): self
    {
        $attributeSchema = $this->schema->query($attribute);
        $mutator = $this->loadMutator($attribute);

        $value = $mutator && $mutator->setter ? $mutator->setter->__invoke($value, new MutatorContext($this, $attribute, $attributeSchema)) : $value;

        if ($attributeSchema) {
            if ($attributeSchema instanceof SchemaRelationAttribute) {
                return $this->setRelation($attribute, $attributeSchema->parse($value));
            }

            return $this->setRawAttribute($attribute, $attributeSchema->parse($value));
        }

        return $this->setRawAttribute($attribute, $value);
    }

    public function get(string $attribute)
    {
        $attributeSchema = $this->schema->query($attribute);

        if ($attributeSchema && $attributeSchema instanceof SchemaRelationAttribute) {
            return $this->getRelation($attribute);
        }

        $value = $this->getRawAttribute($attribute);
        $mutator = $this->loadMutator($attribute);

        return $mutator && $mutator->getter ? $mutator->getter->__invoke($value, new MutatorContext($this, $attribute, $attributeSchema)) : $value;
    }

    public function jsonSerialize(): array
    {
        $serialized = [];

        foreach ($this->schema->getAttributes() as $schema) {
            /** @var SchemaAttribute $schema */
            if ($schema->isHidden()) {
                continue;
            }

            $serialized[$schema->getName()] = $schema->serialize($this->get($schema->getName()));
        }

        return $serialized;
    }

    //

    protected function getRawAttribute(string $name)
    {
        return $this->data[$name] ?? null;
    }

    protected function setRawAttribute(string $name, $value): self
    {
        $this->data[$name] = $value;
        return $this;
    }

    protected function setRawAttributes(array $values): self
    {
        foreach ($values as $name => $value) {
            $this->setRawAttribute($name, $value);
        }

        return $this;
    }

    protected function setRelation(string $name, $value): self
    {
        $this->relations[$name] = $value;
        return $this;
    }

    protected function getRelation(string $name)
    {
        return $this->relations[$name] ?? null;
    }

    protected function schemaLoadDefaults(): void
    {
        foreach ($this->schema->getAttributes() as $schema) {
            /** @var SchemaAttribute $schema */
            if ($schema->hasDefault()) {
                $this->set($schema->getName(), $schema->getDefault());
            }
        }
    }

    protected function loadMutator(string $name): ?Mutator
    {
        $mutator = $this->mutators[$name] ?? null;

        if (!$mutator && is_callable([$this, $name])) {
            $mutator = call_user_func([$this, $name]);

            if (!$mutator instanceof Mutator) {
                throw new UnexpectedValueException("Expected value from mutator '$name' to be instance of Mutator");
            } else {
                $this->mutators[$name] = $mutator;
            }
        }

        return $mutator;
    }

    //

    public static function parse(array $data): self
    {
        $model = new static();
        return $model->fill($data);
    }

    public static function tryParse(array $data): ?self
    {
        try {
            return self::parse($data);
        } catch (SchemaAttributeParseException | MutatorAttributeException $e) {
            return null;
        }
    }
}
