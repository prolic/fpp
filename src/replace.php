<?php

declare(strict_types=1);

namespace Fpp;

use Fpp\ClassKeyword\AbstractKeyword;
use Fpp\ClassKeyword\FinalKeyword;
use Fpp\ClassKeyword\NoKeyword;

const replace = '\Fpp\replace';

function replace(
    Definition $definition,
    ?Constructor $constructor,
    string $template,
    DefinitionCollection $collection,
    ClassKeyword $keyword
): string {
    if ($constructor) {
        $fqcn = $definition->name();

        if ($definition->namespace()) {
            $fqcn = $definition->namespace() . '\\' . $fqcn;
        }

        $constructorClass = str_replace($definition->namespace() . '\\', '', $constructor->name());

        if (false === strpos($constructorClass, '\\')) {
            $baseClass = $definition->name();
        } else {
            $baseClass = $fqcn;
        }

        if (isScalarConstructor($constructor)) {
            $className = $definition->name();
        } else {
            $pos = strrpos($constructor->name(), '\\');

            if (false !== $pos) {
                $className = substr($constructor->name(), $pos + 1);
            } else {
                $className = $constructor->name();
            }
        }
    } else {
        $className = $definition->name();
    }

    $template = str_replace('{{namespace_name}}', $definition->namespace(), $template);
    $template = str_replace('{{class_name}}', $className, $template);
    $template = str_replace('{{variable_name}}', lcfirst($definition->name()), $template);
    switch ($keyword->toString()) {
        case AbstractKeyword::VALUE:
            $template = str_replace('{{abstract_final}}', 'abstract ', $template);
            break;
        case FinalKeyword::VALUE:
            $template = str_replace('{{abstract_final}}', 'final ', $template);
            break;
        case NoKeyword::VALUE:
            $template = str_replace('{{abstract_final}}', '', $template);
            break;
    }

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\AggregateChanged::VALUE:
                    // aggregate changed has always exactly one constructor
                    $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                    $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition), $template);
                    $template = str_replace('{{properties}}', buildProperties($constructor), $template);
                    $template = str_replace('{{message_name}}', buildMessageName($definition), $template);
                    $template = str_replace('{{accessors}}', buildEventAccessors($definition, $collection), $template);
                    $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, false), $template);
                    $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, false), $template);
                break;
            case Deriving\Command::VALUE:
                // command has always exactly one constructor
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\Command', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition), $template);
                $template = str_replace('{{message_name}}', buildMessageName($definition), $template);
                $template = str_replace('{{accessors}}', buildPayloadAccessors($definition, $collection), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\DomainEvent::VALUE:
                // domain event has always exactly one constructor
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition), $template);
                $template = str_replace('{{properties}}', buildProperties($constructor), $template);
                $template = str_replace('{{message_name}}', buildMessageName($definition), $template);
                $template = str_replace('{{accessors}}', buildEventAccessors($definition, $collection), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\Equals::VALUE:
                if ($constructor) {
                    $template = str_replace('{{equals_body}}', buildEqualsBody($constructor, lcfirst($definition->name()), $collection), $template);
                }
                break;
        }
    }

    //@todo
    if (false !== strstr($template, '{{to_string_body}}')) {
        // only one constructor assumed @todo fix
        $constructor = $definition->constructors()[0];
        // only one argument assumed (rightfully)
        if ('String' === $constructor->name()) {
            $template = str_replace('{{to_string_body}}', 'return $this->value;', $template);
        } elseif (1 === count($constructor->arguments())) {
            $argument = $constructor->arguments()[0];
            if ('string' === $argument->type()) {
                $template = str_replace('{{to_string_body}}', 'return $this->value;', $template);
            } elseif (! $argument->isScalartypeHint()) {
                $template = str_replace('{{to_string_body}}', 'return $this->value->toString();', $template);
            }
        }
    }

    if ($constructor && $fqcn !== $constructor->name() && ! isScalarConstructor($constructor)) {
        $template = str_replace('{{class_extends}}', ' extends ' . $baseClass, $template);
    }

    $template = str_replace('{{class_extends}}', '', $template);

    return $template . "\n";
}
