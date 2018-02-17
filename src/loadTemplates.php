<?php

declare(strict_types=1);

namespace Fpp;

const loadTemplates = '\Fpp\loadTemplates';

function loadTemplates(DefinitionCollection $collection, callable $mapToClassTemplate, callable $mapToBodyTemplates): array
{
    $templates = [];
    $cache = [];

    foreach ($collection->definitions() as $definition) {
        $ns = $definition->namespace();
        $name = $definition->name();

        $template = $mapToClassTemplate($definition);

        if (! isset($cache[$template])) {
            $cache[$template] = file_get_contents($template);
        }

        $templates[$ns][$name]['class_template'] = $cache[$template];
        $templates[$ns][$name]['body_templates'] = [];

        foreach ($mapToBodyTemplates($definition) as $template) {
            if (! isset($cache[$template])) {
                $cache[$template] = file_get_contents($template);
            }

            $templates[$ns][$name]['body_templates'][] = $cache[$template];
        }
    }

    return $templates;
}
