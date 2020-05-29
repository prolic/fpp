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
    return new TypeConfiguration(null, null, fromPhpValue, toPhpValue, validator, validationErrorMessage);
}

const fromPhpValue = 'Fpp\Type\DateTimeImmutable\fromPhpValue';

function fromPhpValue(string $paramName): string
{
    return "$$paramName,\n";
}

const toPhpValue = 'Fpp\Type\DateTimeImmutable\toPhpValue';

function toPhpValue(string $paramName): string
{
    return $paramName . '->format(\'Y-m-d\TH:i:s.uP\')';
}

const validator = 'Fpp\Type\DateTimeImmutable\validator';

function validator(string $paramName): string
{
    return <<<CODE
$$paramName = \DateTimeImmutable::createFromFormat(
    'Y-m-d\TH:i:s.uP',
    $$paramName,
    new \DateTimeZone('UTC'))

CODE;
}

const validationErrorMessage = 'Fpp\Type\DateTimeImmutable\validationErrorMessage';

function validationErrorMessage($paramName): string
{
    return "Error on \"$paramName\", datetime string expected";
}

class DateTimeImmutable implements FppType
{
    use TypeTrait;
}
