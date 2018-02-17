<?php

declare(strict_types=1);

namespace Fpp;

const dump = '\Fpp\dump';

function dump(DefinitionCollection $collection, callable $loadTemplates, callable $replace): string
{
    $code = '';

    $templates = $loadTemplates($collection, mapToClassTemplate, mapToBodyTemplates);

    foreach ($collection->definitions() as $definition) {
        $ns = $definition->namespace();
        $name = $definition->name();
        $bodyTemplate = '';

        foreach ($templates[$ns][$name]['body_templates'] as $template) {
            $bodyTemplate .= $template . "\n";
        }

        if (! empty($bodyTemplate)) {
            $bodyTemplate = "\n$bodyTemplate";
        }

        $template = str_replace("        {{body}}\n", $bodyTemplate, $templates[$ns][$name]['class_template']);
        $code .= $replace($definition, $template);
    }

    return $code;
}
