<?php

declare(strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace(Definition $definition, string $template, DefinitionCollection $collection): string
{
    $template = str_replace('{{namespace_name}}', $definition->namespace(), $template);
    $template = str_replace('{{class_name}}', $definition->name(), $template);
    $template = str_replace('{{variable_name}}', lcfirst($definition->name()), $template);

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
                $template = str_replace('{{abstract_final}}', 'abstract ', $template);
                break;
            case Deriving\AggregateChanged::VALUE:
                    // aggregate changed has always exactly one constructor
                    $constructor = $definition->constructors()[0];
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
                $constructor = $definition->constructors()[0];
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\Command', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition), $template);
                $template = str_replace('{{message_name}}', buildMessageName($definition), $template);
                $template = str_replace('{{accessors}}', buildPayloadAccessors($definition, $collection), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\DomainEvent::VALUE:
                // domain event has always exactly one constructor
                $constructor = $definition->constructors()[0];
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition), $template);
                $template = str_replace('{{properties}}', buildProperties($constructor), $template);
                $template = str_replace('{{message_name}}', buildMessageName($definition), $template);
                $template = str_replace('{{accessors}}', buildEventAccessors($definition, $collection), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\Equals::VALUE:
                // @todo only one constructor for now
                $constructor = $definition->constructors()[0];
                $template = str_replace('{{equals_body}}', buildEqualsBody($constructor, lcfirst($definition->name()), $collection), $template);
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

    // cleanup nonused placeholders
    $template = str_replace('{{abstract_final}}', '', $template);
    $template = str_replace('{{class_extends}}', '', $template);

    return $template;
}
