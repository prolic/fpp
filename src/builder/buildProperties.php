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
use Fpp\Deriving;

const buildProperties = '\Fpp\Builder\buildProperties';

function buildProperties(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    $properties = '';

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\Query())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            $properties = "protected \$messageName = '"
                . buildMessageName($definition, $constructor, $collection, 'message_name') . "';\n\n";
        }

        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            $properties .= "    protected \$payload = [];\n\n";
        }

        if ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\Query())
        ) {
            return $properties;
        }
    }

    foreach ($constructor->arguments() as $argument) {
        if (! empty($properties)) {
            $properties .= '    ';
        }

        $properties .= 'private $' . $argument->name() . ";\n";
    }

    return \ltrim($properties);
}
