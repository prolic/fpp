<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
