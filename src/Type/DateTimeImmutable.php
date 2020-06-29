<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\DateTimeImmutable;

use Fpp\Type as FppType;
use Fpp\TypeConfiguration;
use Fpp\TypeTrait;

function typeConfiguration(): TypeConfiguration
{
    return new TypeConfiguration(
        null,
        null,
        fromPhpValue,
        toPhpValue,
        validator,
        validationErrorMessage,
        equals
    );
}

const fromPhpValue = 'Fpp\Type\DateTimeImmutable\fromPhpValue';

function fromPhpValue(string $type, string $paramName): string
{
    return "$type::createFromFormat('Y-m-d\TH:i:s.uP', $paramName, new \DateTimeZone('UTC'))";
}

const toPhpValue = 'Fpp\Type\DateTimeImmutable\toPhpValue';

function toPhpValue(string $type, string $paramName): string
{
    return $paramName . '->format(\'Y-m-d\TH:i:s.uP\')';
}

const validator = 'Fpp\Type\DateTimeImmutable\validator';

function validator(string $type, string $paramName): string
{
    return <<<CODE
$paramName = $type::createFromFormat('Y-m-d\TH:i:s.uP', $paramName, new \DateTimeZone('UTC'))

CODE;
}

const validationErrorMessage = 'Fpp\Type\DateTimeImmutable\validationErrorMessage';

function validationErrorMessage(string $paramName): string
{
    return "Error on \"$paramName\", datetime string expected";
}

const equals = 'Fpp\Type\DateTimeImmutable\equals';

function equals(string $paramName, string $otherParamName): string
{
    return "$paramName == $otherParamName";
}

class DateTimeImmutable implements FppType
{
    use TypeTrait;
}
