<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\String_;

use function Fpp\buildDefaultPhpFile;
use function Fpp\char;
use Fpp\Configuration;
use Fpp\Definition;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\result;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use function Fpp\Type\Marker\markers;
use Fpp\TypeConfiguration;
use function Fpp\typeName;
use Fpp\TypeTrait;
use Nette\PhpGenerator\Type;

function typeConfiguration(): TypeConfiguration
{
    return new TypeConfiguration(parse, build, fromPhpValue, toPhpValue, validator, validationErrorMessage);
}

const parse = 'Fpp\Type\String_\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('string')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result([]))
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms) => new String_($t, $ms), $t, $ms);
}

const build = 'Fpp\Type\String_\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof String_) {
        throw new \InvalidArgumentException('Can only build definitions of ' . String_::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal()
        ->setImplements($type->markers());

    $class->addProperty('value')->setType(Type::STRING)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::STRING);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::STRING);
    $method->setBody('return $this->value;');

    return [$fqcn => $file];
}

const fromPhpValue = 'Fpp\Type\String_\fromPhpValue';

function fromPhpValue(String_ $type, string $paramName): string
{
    return 'new ' . $type->classname() . '(' . $paramName . ')';
}

const toPhpValue = 'Fpp\Type\String_\toPhpValue';

function toPhpValue(String_ $type, string $paramName): string
{
    return $paramName . '->value()';
}

const validator = 'Fpp\Type\String_\validator';

function validator(string $paramName): string
{
    return "\is_string(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\String_\validationErrorMessage';

function validationErrorMessage($paramName): string
{
    return "Error on \"$paramName\", string expected";
}

class String_ implements FppType
{
    use TypeTrait;
}
