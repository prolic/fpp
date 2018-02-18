<?php

declare(strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace(Definition $definition, string $template): string
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
                    $template = str_replace('{{class_extends}}', ' extends \Prooph\Common\Messaging\DomainEvent', $template);
                    $messageName = $definition->messageName();
                    if (null === $messageName) {
                        $messageName = $definition->namespace() . '\\' . $definition->name();
                    }
                    $template = str_replace('{{message_name}}', $messageName, $template);
                    $arguments = '';
                    $staticConstructorBody = '';
                    $payload = '';
                    $payloadValidation = '';

                    $constructorArguments = $definition->constructors()[0]->arguments();
                    if ($constructorArguments[0]->isScalartypeHint()) {
                        $aggregateId = '$' . $constructorArguments[0]->name();
                    } else {
                        $aggregateId = '$' . $constructorArguments[0]->name() . '->toString()';
                    }
                    foreach ($constructorArguments as $key => $argument) {
                        $isNullCheck = '';
                        if ($argument->nullable()) {
                            $arguments .= '?';
                            $isNullCheck = "isset(\$payload['{$argument->name()}']) && ";
                        }
                        if ($argument->isScalartypeHint()) {
                            $arguments .= $argument->type() . ' $' . $argument->name() . ', ';
                            if (0 === $key) {
                                // first argument is the aggregate id
                                continue;
                            }
                            $payload .= "                '{$argument->name()}' => \${$argument->name()},\n";
                            if (! $argument->nullable()) {
                                $payloadValidation .= <<<STRING

            if (! isset(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Key '{$argument->name()}' is missing in payload");
            }

STRING;
                            }

                            $payloadValidation .= <<<STRING

            if ($isNullCheck! is_{$argument->type()}(\$payload['{$argument->name()}'])) {
                throw new \InvalidArgumentException("Value for '{$argument->name()}' is not a {$argument->type()} in payload");
            }

STRING;
                        }
                    }
                    if (! empty($payload)) {
                        $staticConstructorBody = "return new self($aggregateId, [\n" . $payload . "            ]);";
                    }
                    $template = str_replace('{{arguments}}', substr($arguments, 0, -2), $template);
                    $template = str_replace('{{static_constructor_body}}', $staticConstructorBody, $template);
                    $template = str_replace('            {{payload_validation}}', substr($payloadValidation, 1), $template);
                break;
        }
    }

    $template = str_replace('{{abstract_final}}', '', $template);

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

    $template = str_replace('{{class_extends}}', '', $template);

    return $template;
}
