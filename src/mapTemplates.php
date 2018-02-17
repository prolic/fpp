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

const mapToClassTemplate = '\Fpp\mapToClassTemplate';

function mapToClassTemplate (Definition $definition): string
{
    $prefix = __DIR__ . '/templates/';
    $constructors = $definition->constructors();
    $derivings = $definition->derivings();

    if (1 === count($constructors)) {
        switch ($constructors[0]->name()) {
            case 'String':
                return $prefix . 'string.template';
            case 'Int':
                return $prefix . 'int.template';
            case 'Bool':
                return $prefix . 'bool.template';
            case 'Float':
                return $prefix . 'float.template';
        }
    }

    foreach ($derivings as $deriving) {
        switch ((string) $deriving) {
            case AggregateChanged::VALUE:
                break;
            case Command::VALUE:
                break;
            case DomainEvent::VALUE:
                break;
            case Query::VALUE:
                break;
            case Enum::VALUE:
                return $prefix . 'enum.template';
            case Uuid::VALUE:
                return $prefix . 'uuid.template';
            default:
                return $prefix . 'class.template';
        }
    }

    return $prefix . 'class.template';
}

const mapToBodyTemplates = '\Fpp\mapToBodyTemplates';

/**
 * @param Definition $definition
 * @return string[]
 */
function mapToBodyTemplates (Definition $definition): array
{
    $prefix = __DIR__ . '/templates/';

    $templates = [];

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Equals::VALUE:
                $templates[] = $prefix . 'equals.template';
                break;
            case FromArray::VALUE:
                $templates[] = $prefix . 'from_array.template';
                break;
            case FromScalar::VALUE:
                $templates[] = $prefix . 'from_scalar.template';
                break;
            case FromString::VALUE:
                $templates[] = $prefix . 'from_string.template';
                break;
            case ToArray::VALUE:
                $templates[] = $prefix . 'to_array.template';
                break;
            case ToScalar::VALUE:
                $templates[] = $prefix . 'to_scalar.template';
                break;
            case ToString::VALUE:
                $templates[] = $prefix . 'to_string.template';
                break;
        }
    }

    return $templates;
}
