<?php

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
use Fpp\Deriving\Query;
use Fpp\Deriving\ToArray;
use Fpp\Deriving\ToScalar;
use Fpp\Deriving\ToString;
use Fpp\Deriving\Uuid;

const loadTemplate = '\Fpp\loadTemplate';

function loadTemplate(Definition $definition): string
{
    static $cache = [];

    $prefix = __DIR__ . '/templates/';
    $constructors = $definition->constructors();

    $classTemplateFile = $prefix . 'class.template';
    $bodyTemplatesFiles = [];

    if (1 === count($constructors)) {
        switch ($constructors[0]->name()) {
            case 'String':
            case 'Int':
            case 'Bool':
            case 'Float':
                $bodyTemplatesFiles[] = $prefix . strtolower($constructors[0]->name()) . '.template';
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
            case Enum::VALUE:
            case Uuid::VALUE:
            case Equals::VALUE:
            case FromArray::VALUE:
            case FromScalar::VALUE:
            case FromString::VALUE:
            case ToArray::VALUE:
            case ToScalar::VALUE:
            case ToString::VALUE:
                $bodyTemplatesFiles[] = $prefix . strtolower((string) $deriving) . '.template';
                break;
        }
    }

    $bodyTemplate = '';

    foreach ($bodyTemplatesFiles as $file) {
        if (! isset($cache[$file])) {
            $cache[$file] = file_get_contents($file);
        }

        $bodyTemplate .= $cache[$file] . "\n\n";;
    }

    if (! empty($bodyTemplate)) {
        $bodyTemplate = substr($bodyTemplate, 0, -1);
    }

    return str_replace("        {{body}}\n", $bodyTemplate, $classTemplate);
}
