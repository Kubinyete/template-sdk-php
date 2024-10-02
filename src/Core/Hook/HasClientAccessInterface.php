<?php

namespace Kubinyete\TemplateSdkPhp\Core\Hook;

use Kubinyete\TemplateSdkPhp\Core\Client;

interface HasClientAccessInterface
{
    function useClient(Client $client): void;
}
