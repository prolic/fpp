<?php

declare (strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace (Definition $definition, string $template): string
{
    $template = str_replace('{{namespace_name}}', $definition->namespace(), $template);
    $template = str_replace('{{class_name}}', $definition->name(), $template);
    $template = str_replace('{{variable_name}}', lcfirst($definition->name()), $template);

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

    return $template;
}
