<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Fpp\Deriving\AggregateChanged;
use Fpp\Deriving\Command;
use Fpp\Deriving\DomainEvent;
use Fpp\Deriving\Enum;
use Fpp\Deriving\Equals;
use Fpp\Deriving\FromArray;
use Fpp\Deriving\FromScalar;
use Fpp\Deriving\FromString;
use Fpp\Deriving\MicroAggregateChanged;
use Fpp\Deriving\Query;
use Fpp\Deriving\ToArray;
use Fpp\Deriving\ToScalar;
use Fpp\Deriving\ToString;
use Fpp\Deriving\Uuid;

const loadTemplate = '\Fpp\loadTemplate';

function loadTemplate(Definition $definition, ?Constructor $constructor): string
{
    static $cache = [];

    $prefix = __DIR__ . '/templates/';
    $markerTemplateFile = $prefix . 'marker.template';
    if ($definition->isMarker()) {
        if (! isset($cache[$markerTemplateFile])) {
            $cache[$markerTemplateFile] = file_get_contents($markerTemplateFile);
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
        $cache[$classTemplateFile] = file_get_contents($classTemplateFile);
    }

    $classTemplate = $cache[$classTemplateFile];

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case AggregateChanged::VALUE:
            case Command::VALUE:
            case DomainEvent::VALUE:
            case Query::VALUE:
            case MicroAggregateChanged::VALUE:
            case Uuid::VALUE:
            case Equals::VALUE:
            case FromArray::VALUE:
            case FromScalar::VALUE:
            case FromString::VALUE:
            case ToArray::VALUE:
            case ToScalar::VALUE:
            case ToString::VALUE:
                $bodyTemplatesFiles[] = $prefix . \strtolower((string) $deriving) . '.template';
                break;
            case Enum::VALUE:
                if (null === $constructor) {
                    $bodyTemplatesFiles[] = $prefix . 'enum.template';
                }
                break;
        }
    }

    $bodyTemplate = '';

    foreach ($bodyTemplatesFiles as $file) {
        if (! isset($cache[$file])) {
            $cache[$file] = file_get_contents($file);
        }

        $bodyTemplate .= $cache[$file] . "\n\n";
    }

    if (! empty($bodyTemplate)) {
        $bodyTemplate = \substr($bodyTemplate, 0, -1);
    }

    return \str_replace("    {{body}}\n", $bodyTemplate, $classTemplate);
}
