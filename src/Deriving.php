<?php

declare(strict_types=1);

namespace Fpp;

use MabeEnum\Enum;

/**
 * @method static Deriving SHOW()
 * @method static Deriving STRING_CONVERTER()
 * @method static Deriving ARRAY_CONVERTER()
 * @method static Deriving VALUE_OBJECT()
 */
final class Deriving extends Enum
{
    const SHOW = 'Show';
    const STRING_CONVERTER = 'StringConverter';
    const ARRAY_CONVERTER = 'ArrayConverter';
    const VALUE_OBJECT = 'ValueObject';
}
