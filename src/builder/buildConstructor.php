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
use function Fpp\buildDocBlockArgumentTypes;

const buildConstructor = '\Fpp\Builder\buildConstructor';

function buildConstructor(Definition $definition, ?Constructor $constructor, DefinitionCollection $collection, string $placeHolder): string
{
    if (null === $constructor) {
        return $placeHolder;
    }

    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\AggregateChanged())
            || $deriving->equals(new Deriving\Command())
            || $deriving->equals(new Deriving\DomainEvent())
            || $deriving->equals(new Deriving\Query())
            || $deriving->equals(new Deriving\MicroAggregateChanged())
        ) {
            return $placeHolder;
        }
    }

    $argumentList = buildArguments($definition, $constructor, new DefinitionCollection(), '');

    if ('' === $argumentList) {
        return $placeHolder;
    }

    $hasExceptionDeriving = false;
    foreach ($definition->derivings() as $deriving) {
        if ($deriving->equals(new Deriving\Exception())) {
            $hasExceptionDeriving = true;
            break;
        }
    }
    $docblock = buildDocBlockArgumentTypes($constructor->arguments(), '', $hasExceptionDeriving);

    if ($docblock) {
        $docblock = \substr($docblock, 4) . '    ';
    }

    $code = $docblock . "public function __construct($argumentList)\n    {\n";
    $printed = false;

    foreach ($definition->conditions() as $condition) {
        if ('_' === $condition->constructor()
            || false !== \strrpos($constructor->name(), $condition->constructor())
        ) {
            $printed = true;
            $code .= <<<CODE
        if ({$condition->code()}) {
            throw new \\InvalidArgumentException('{$condition->errorMessage()}');
        }


CODE;
        }
    }

    foreach ($constructor->arguments() as $key => $argument) {
        if ($argument->isList() && 1 !== \count($constructor->arguments())) {
            if ($printed && \substr($code, -2) !== "\n\n") {
                $code .= "\n";
            }

            $printed = true;

            if ($argument->nullable()) {
                $code .= "        if (\${$argument->name()} !== null) {\n";
            }

            $code .= "            \$this->{$argument->name()} = [];\n";
            $code .= "            foreach (\${$argument->name()} as \$__value) {\n";
            $code .= '                if (! ';

            if ($argument->isScalartypeHint()) {
                $floatCheck = '';
                if ($argument->type() === 'float') {
                    $floatCheck = ' && ! \is_int($__value)';
                }
                $code .= "\is_{$argument->type()}(\$__value)$floatCheck) {\n";
            } else {
                $type = '\\' . $argument->type();
                $code .= "\$__value instanceof $type) {\n";
            }

            $code .= "                    throw new \InvalidArgumentException('{$argument->name()} expected an array of {$argument->type()}');\n";
            $code .= "                }\n";
            $code .= "                \$this->{$argument->name()}[] = \$__value;\n";
            $code .= "            }\n";
            if ($argument->nullable()) {
                $code .= "        }\n";
            }
            $code .= "\n";
        } else {
            $code .= "        \$this->{$argument->name()} = \${$argument->name()};\n";
        }
    }
    if ($hasExceptionDeriving) {
        $code .= "        parent::__construct(\$message, \$code, \$previous);\n";
    }
    $code .= "    }\n";

    return $code;
}
