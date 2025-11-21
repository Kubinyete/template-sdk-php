<?php

namespace Teamipag\Sdk\Core\Hook;

use Teamipag\Sdk\Core\Client;

interface HasClientAccessInterface
{
    function useClient(Client $client): void;
}
