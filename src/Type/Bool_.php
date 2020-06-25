<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Bool_;

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
    return new TypeConfiguration(
        parse,
        build,
        fromPhpValue,
        toPhpValue,
        validator,
        validationErrorMessage,
        equals
    );
}

const parse = 'Fpp\Type\Bool_\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('bool')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result([]))
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms) => new Bool_($t, $ms), $t, $ms);
}

const build = 'Fpp\Type\Bool_\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof Bool_) {
        throw new \InvalidArgumentException(
            'Can only build definitions of ' . Bool_::class
        );
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal()
        ->setImplements($type->markers());

    $class->addProperty('value')->setType(Type::BOOL)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::BOOL);
    $constructor->setBody('$this->value = $value;');

    $boolConstructor = $class->addMethod('true')->setReturnType(Type::SELF)->setStatic();
    $boolConstructor->setBody('return new self(true);');

    $falseConstructor = $class->addMethod('false')->setReturnType(Type::SELF)->setStatic();
    $falseConstructor->setBody('return new self(false);');

    $method = $class->addMethod('value')->setReturnType(Type::BOOL);
    $method->setBody('return $this->value;');

    $method = $class->addMethod('equals')->setReturnType(Type::BOOL);
    $method->addParameter('other')->setType(Type::SELF)->setNullable();
    $method->setBody('return null !== $other && $this->value === $other->value;');

    return [$fqcn => $file];
}

const fromPhpValue = 'Fpp\Type\Bool_\fromPhpValue';

function fromPhpValue(Bool_ $type, string $paramName): string
{
    return 'new ' . $type->classname() . '(' . $paramName . ')';
}

const toPhpValue = 'Fpp\Type\Bool_\toPhpValue';

function toPhpValue(Bool_ $type, string $paramName): string
{
    return $paramName . '->value()';
}

const validator = 'Fpp\Type\Bool_\validator';

function validator(string $paramName): string
{
    return "\is_bool(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Bool_\validationErrorMessage';

function validationErrorMessage(string $paramName): string
{
    return "Error on \"$paramName\", bool expected";
}

const equals = 'Fpp\Type\Bool_\equals';

function equals(string $paramName, string $otherParamName): string
{
    return "{$paramName}->equals($otherParamName)";
}

class Bool_ implements FppType
{
    use TypeTrait;
}
