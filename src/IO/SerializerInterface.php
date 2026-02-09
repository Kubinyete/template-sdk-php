<?php

namespace Teamipag\Sdk\IO;

interface SerializerInterface
{
    /**
     * @param array<array-key,mixed> $data
     * @return string
     */
    function serialize(array $data): string;

    /**
     * @param string $data
     * @return array<array-key,mixed>
     */
    function unserialize(string $data): array;
    function getContentType(): ?string;
}
