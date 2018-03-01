<?php

declare(strict_types=1);

namespace Fpp\Builder;

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;

const buildMessageName = '\Fpp\Builder\buildMessageName';

function buildMessageName(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    $messageName = $definition->messageName();

    if (null === $messageName) {
        $messageName = $definition->namespace();

        if ('' !== $messageName) {
            $messageName .= '\\';
        }

        $messageName .= $definition->name();
    }

    return $messageName;
}
