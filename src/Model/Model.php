<?php

namespace Kubinyete\TemplateSdkPhp\Model;

use Closure;
use UnexpectedValueException;
use Kubinyete\TemplateSdkPhp\Util\ClassUtil;
use Kubinyete\TemplateSdkPhp\Model\Schema\Schema;
use Kubinyete\TemplateSdkPhp\Model\Schema\Mutator;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaBuilder;
use Kubinyete\TemplateSdkPhp\Model\Schema\MutatorContext;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaAttribute;
use Kubinyete\TemplateSdkPhp\Model\SerializableModelInterface;
use Kubinyete\TemplateSdkPhp\Model\Schema\SchemaRelationAttribute;
use Kubinyete\TemplateSdkPhp\Model\Schema\Exception\MutatorAttributeException;
use Kubinyete\TemplateSdkPhp\Model\Schema\Exception\SchemaAttributeParseException;

abstract class Model implements SerializableModelInterface
{
    private Schema $schema;
    private array $data;
    private array $relations;
    private string $name;
    private array $mutators;

    private static array $globalSchema;

    public function __construct(array $data = [], array $relations = [], ?string $name = null)
    {
        $this->data = $data;
        $this->relations = $relations;
        $this->name = $name ?? $this->useDefaultName();

        $this->mutators = [];

        $this->schema = $this->useContextualSchema();
        $this->schemaLoadDefaults();
    }

    //

    private function useDefaultName(): string
    {
        return basename(str_replace('\\', '/', get_class($this)));
    }

    private function useContextualSchema(): Schema
    {
        $schema = static::$globalSchema[static::class] ?? null;
        if (is_null($schema)) {
            $schema = static::$globalSchema[static::class] = new Schema($this->name);
            $this->schema($schema->builder());
        }
        return $schema;
    }

    protected abstract function schema(SchemaBuilder $schema);

    //

    public function getModelName(): string
    {
        return $this->name;
    }

    public function setModelName(string $name): self
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

        foreach ($this->schema->getAttributes() as $attr) {
            /** @var SchemaAttribute $schema */
            if ($attr->isRequired() && !array_key_exists($attr->getName(), $data)) {
                throw new SchemaAttributeParseException($attr, "Missing required attribute");
            }
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

    public function toArray(): array
    {
        $mapper = static function (Closure $mapper, array $items): array {
            return array_map(
                static fn($item) => is_iterable($item) ? $mapper($mapper, $item) : $item->toArray(),
                $items
            );
        };

        return array_merge(
            $this->getAttributes(),
            $mapper($mapper, $this->getRelations())
        );
    }

    //

    public function getAttributes(): array
    {
        return $this->data;
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getAllAttributes(): array
    {
        return array_merge($this->data, $this->relations);
    }

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

    protected function schemaMutate(Closure $callback): void
    {
        if ($this->schema === (static::$globalSchema[static::class] ?? null)) {
            $this->schema = clone $this->schema;
        }

        $callback($this->schema->builder());
    }

    //

    public static function parse(array $data): static
    {
        return self::make()->fill($data);
    }

    public static function tryParse(array $data): ?static
    {
        try {
            return static::parse($data);
        } catch (SchemaAttributeParseException | MutatorAttributeException $e) {
            return null;
        }
    }

    public static function make(): static
    {
        return new static();
    }
}
