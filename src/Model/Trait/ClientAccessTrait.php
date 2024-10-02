<?php

namespace Kubinyete\TemplateSdkPhp\Model\Trait;

use Closure;
use Throwable;
use UnexpectedValueException;
use Kubinyete\TemplateSdkPhp\Core\Client;
use Kubinyete\TemplateSdkPhp\Core\Hook\HasClientAccessInterface;

trait ClientAccessTrait
{
    private ?Client $client = null;

    public function useClient(Client $client): void
    {
        $this->client = $client;
        $mapper = static function (Closure $mapper, Client $client, $item) {
            if ($item instanceof HasClientAccessInterface) {
                $item->useClient($client);
            } else if (is_iterable($item)) {
                foreach ($item as $x) $mapper($mapper, $client, $x);
            }
        };

        /** @var Model $self */
        $self = $this;
        $mapper($mapper, $client, $self->getRelations());
    }

    protected function usingClient(Closure $closure): mixed
    {
        if (!$this->client) {
            /** @var Model $self */
            $self = $this;
            $type = get_class($self);
            throw new UnexpectedValueException("Model {$self->getModelName()}@{$type} requested client access but is currently missing references");
        }

        // Reinstantiate any pending client references that needs to be set on newer models after an action
        // has already been dispatched.
        $returnValue = $closure($this->client);
        $this->useClient($this->client);
        return $returnValue;
    }
}
