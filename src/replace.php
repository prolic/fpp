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
    ClassKeyword $keyword,
    array $builders = null
): string {
    if (null === $builders) {
        $builders = defaultBuilders();
    }

    if ($constructor) {
        if (isScalarConstructor($constructor)) {
            $namespace = $definition->namespace();
        } else {
            $position = strrpos($constructor->name(), '\\');

            if (false === $position) {
                $namespace = '';
            } else {
                $namespace = substr($constructor->name(), 0, $position);
            }
        }
    } else {
        $namespace = $definition->namespace();
    }

    $template = str_replace('{{namespace_name}}', $namespace, $template);

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

    foreach ($builders as $placeHolder => $builder) {
        $template = str_replace('{{' . $placeHolder . '}}', $builder($definition, $constructor, $collection), $template);
    }

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\AggregateChanged::VALUE:
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition, true), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, false), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, false), $template);
                break;
            case Deriving\Command::VALUE:
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\Command', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition, true), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\DomainEvent::VALUE:
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition, true), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\Enum::VALUE:
                if ($constructor) {
                    $template = str_replace('{{enum_value}}', buildReferencedClass($namespace, $constructor->name()), $template);
                } else {
                    $replace = '';
                    foreach ($definition->constructors() as $definitionConstructor) {
                        $class = buildReferencedClass($namespace, $definitionConstructor->name());
                        $replace .= "            $class::VALUE => $class::class,\n";
                    }
                    $template = str_replace('{{enum_options}}', substr($replace, 12, -1), $template);
                }
                break;
            case Deriving\FromArray::VALUE:
                $template = str_replace('{{from_array_body}}', buildFromArrayBody($constructor, $definition, $collection), $template);
                break;
            case Deriving\FromScalar::VALUE:
                if (isScalarConstructor($constructor)) {
                    $type = strtolower($constructor->name());
                } else {
                    $argument = $constructor->arguments()[0];
                    $type = strtolower($argument->type());
                }

                $template = str_replace('{{type}}', $type, $template);
                break;
            case Deriving\Query::VALUE:
                $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\Query', $template);
                $template = str_replace('{{arguments}}', buildArgumentList($constructor, $definition, true), $template);
                $template = str_replace('{{static_constructor_body}}', buildStaticConstructorBodyConvertingToPayload($constructor, $collection, true), $template);
                $template = str_replace('{{payload_validation}}', buildPayloadValidation($constructor, $collection, true), $template);
                break;
            case Deriving\ToArray::VALUE:
                $template = str_replace('{{to_array_body}}', buildToArrayBody($constructor, $definition, $collection), $template);
                break;
            case Deriving\ToScalar::VALUE:
                if (isScalarConstructor($constructor)) {
                    $type = strtolower($constructor->name());
                } else {
                    $argument = $constructor->arguments()[0];
                    $type = strtolower($argument->type());
                }

                $template = str_replace('{{type}}', $type, $template);
                $template = str_replace('{{to_scalar_body}}', buildToScalarBody($constructor, $definition, $collection), $template);
                break;
            case Deriving\ToString::VALUE:
                $template = str_replace('{{to_string_body}}', buildToScalarBody($constructor, $definition, $collection), $template);
                break;
            case Deriving\Uuid::VALUE:
                break;
        }
    }

    $fullQualifiedDefinitionClassName = $definition->name();

    if ($definition->namespace()) {
        $fullQualifiedDefinitionClassName = $definition->namespace() . '\\' . $fullQualifiedDefinitionClassName;
    }

    if ($constructor && ! isScalarConstructor($constructor) && $constructor->name() !== $fullQualifiedDefinitionClassName) {
        if ($namespace === $definition->namespace()) {
            $baseClass = $definition->name();
        } else {
            $baseClass = '\\' . $fullQualifiedDefinitionClassName;
        }
        $template = str_replace('{{class_extends}}', ' extends ' . $baseClass, $template);
    }

    $template = str_replace('{{class_extends}}', '', $template);

    if ($constructor) {
        $constructorString = buildConstructor($constructor, $definition);

        if ('' !== $constructorString) {
            $template = str_replace('{{constructor}}', $constructorString, $template);
        }
    }

    foreach ($builders as $placeHolder => $builder) {
        $template = str_replace('{{' . $placeHolder . '}}', '', $template);
    }

    // clean up
    $template = str_replace("        \n", "\n", $template);
    $template = str_replace("    \n", "\n", $template);
    $template = str_replace("\n\n    }\n}", "\n    }\n}", $template);

    return $template . "\n";
}
