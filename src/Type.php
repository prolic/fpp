<?php

declare(strict_types=1);

namespace Fpp;

use MabeEnum\Enum;

/**
 * @method static Type AGGREGATE_CHANGED()
 * @method static Type DATA()
 * @method static Type COMMAND()
 * @method static Type DOMAIN_EVENT()
 * @method static Type QUERY()
 */
final class Type extends Enum
{
    const DATA = 'data';
    const AGGREGATE_CHANGED = 'aggregateChanged';
    const COMMAND = 'command';
    const DOMAIN_EVENT = 'domainEvent';
    const QUERY = 'query';
}
