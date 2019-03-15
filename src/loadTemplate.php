<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

const loadTemplate = '\Fpp\loadTemplate';

function loadTemplate(Definition $definition, ?Constructor $constructor): string
{
    static $cache = [];

    $prefix = __DIR__ . '/templates/';
    $markerTemplateFile = $prefix . 'marker.template';
    if ($definition->isMarker()) {
        if (! isset($cache[$markerTemplateFile])) {
            $cache[$markerTemplateFile] = \file_get_contents($markerTemplateFile);
        }

        return $cache[$markerTemplateFile];
    }

    $classTemplateFile = $prefix . 'class.template';
    $bodyTemplatesFiles = [];

    if (null !== $constructor) {
        switch ($constructor->name()) {
            case 'Bool':
            case 'Float':
            case 'Int':
            case 'String':
                $bodyTemplatesFiles[] = $prefix . \strtolower($constructor->name()) . '.template';
                break;
            case 'Bool[]':
            case 'Float[]':
            case 'Int[]':
            case 'String[]':
                $bodyTemplatesFiles[] = $prefix . \strtolower(\substr($constructor->name(), 0, -2)) . 'list.template';
                break;
        }
    }

    if (! isset($cache[$classTemplateFile])) {
        $cache[$classTemplateFile] = \file_get_contents($classTemplateFile);
    }

    $classTemplate = $cache[$classTemplateFile];

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\AggregateChanged::VALUE:
            case Deriving\Command::VALUE:
            case Deriving\DomainEvent::VALUE:
            case Deriving\Query::VALUE:
            case Deriving\MicroAggregateChanged::VALUE:
            case Deriving\Uuid::VALUE:
            case Deriving\Equals::VALUE:
            case Deriving\FromArray::VALUE:
            case Deriving\FromScalar::VALUE:
            case Deriving\FromString::VALUE:
            case Deriving\ToArray::VALUE:
            case Deriving\ToScalar::VALUE:
            case Deriving\ToString::VALUE:
            case Deriving\Exception::VALUE:
                $bodyTemplatesFiles[] = $prefix . \strtolower((string) $deriving) . '.template';
                break;
            case Deriving\Enum::VALUE:
                if (null === $constructor) {
                    $bodyTemplatesFiles[] = $prefix . 'enum.template';
                }
                break;
        }
    }

    $bodyTemplate = '';

    foreach ($bodyTemplatesFiles as $file) {
        if (! isset($cache[$file])) {
            $cache[$file] = \file_get_contents($file);
        }

        $bodyTemplate .= $cache[$file] . "\n\n";
    }

    if (! empty($bodyTemplate)) {
        $bodyTemplate = \substr($bodyTemplate, 0, -1);
    }

    return \str_replace("    {{body}}\n", $bodyTemplate, $classTemplate);
}
