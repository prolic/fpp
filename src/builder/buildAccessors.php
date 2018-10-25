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
use function Fpp\buildArgumentReturnType;
use function Fpp\buildMethodBodyFromAggregateId;
use function Fpp\buildMethodBodyFromPayload;
use function Fpp\buildDocBlockReturnType;

const buildAccessors = '\Fpp\Builder\buildAccessors';

function buildAccessors(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor
        || 0 === \count($constructor->arguments())
    ) {
        return $placeHolder;
    }

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            $accessors = '';

            foreach ($constructor->arguments() as $index => $argument) {
                $returnType = buildArgumentReturnType($argument, $definition);

                if (0 === $index) {
                    $methodBody = buildMethodBodyFromAggregateId($argument, $definition, $collection, true);
                } else {
                    $methodBody = buildMethodBodyFromPayload($argument, $definition, $collection, true);
                }

                $accessors .= buildDocBlockReturnType($argument);
                $accessors .= <<<CODE
    public function {$argument->name()}()$returnType
    {
        $methodBody
    }


CODE;
            }

            return \ltrim(\substr($accessors, 0, -2));
        } elseif ($deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\Query())
        ) {
            $accessors = '';

            foreach ($constructor->arguments() as $argument) {
                $returnType = buildArgumentReturnType($argument, $definition);
                $methodBody = buildMethodBodyFromPayload($argument, $definition, $collection, false);

                $accessors .= buildDocBlockReturnType($argument);
                $accessors .= <<<CODE
    public function {$argument->name()}()$returnType
    {
        $methodBody
    }


CODE;
            }

            return \ltrim(\substr($accessors, 0, -1));
        }
    }

    // default
    $accessors = '';

    foreach ($constructor->arguments() as $argument) {
        $returnType = buildArgumentReturnType($argument, $definition);

        $accessors .= buildDocBlockReturnType($argument);
        $accessors .= <<<CODE
    public function {$argument->name()}()$returnType
    {
        return \$this->{$argument->name()};
    }


CODE;
    }

    return \ltrim(\substr($accessors, 0, -1));
}
