<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Guid;

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
use function Fpp\typeName;
use Fpp\TypeTrait;
use Nette\PhpGenerator\Type;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Tuple;

function definition(): Tuple
{
    return \Tuple(parse, build, fromPhpValue, toPhpValue, validator, validationErrorMessage);
}

const parse = 'Fpp\Type\Guid\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('guid')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result(Nil()))
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms) => new Guid($t, $ms), $t, $ms);
}

const build = 'Fpp\Type\Guid\build';

function build(Definition $definition, ImmMap $definitions, Configuration $config): ImmMap
{
    $type = $definition->type();

    if (! $type instanceof Guid) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Guid::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal()
        ->setImplements($type->markers()->toArray());

    $namespace = $file->getNamespaces()[$definition->namespace()];
    $namespace->addUse('Ramsey\Uuid\UuidFactory');
    $namespace->addUse('Ramsey\Uuid\FeatureSet');
    $namespace->addUse('Ramsey\Uuid\UuidInterface');

    $class->addProperty('uuid')->setType('UuidInterface')->setPrivate();
    $class->addProperty('factory')->setType('UuidFactory')->setNullable()->setStatic()->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('uuid')->setType('UuidInterface');
    $constructor->setBody('$this->uuid = $uuid;');
    $constructor->setPrivate();

    $generate = $class->addMethod('generate')->setReturnType('self');
    $generate->setBody('return new self(self::factory()->uuid4());');
    $generate->setStatic();

    $fromString = $class->addMethod('fromString')->setReturnType('self');
    $fromString->addParameter('uuid')->setType('string');
    $fromString->setBody('return new self(self::factory()->fromString($uuid));');
    $fromString->setStatic();

    $fromBinary = $class->addMethod('fromBinary')->setReturnType('self');
    $fromBinary->addParameter('bytes')->setType('string');
    $fromBinary->setBody('return new self(self::factory()->fromBytes($bytes));');
    $fromBinary->setStatic();

    $toString = $class->addMethod('toString')->setReturnType(Type::STRING);
    $toString->setBody('return $this->uuid->toString();');

    $__toString = $class->addMethod('__toString')->setReturnType(Type::STRING);
    $__toString->setBody('return $this->uuid->toString();');

    $toBinary = $class->addMethod('toBinary')->setReturnType(Type::STRING);
    $toBinary->setBody('return $this->uuid->getBytes();');

    $equals = $class->addMethod('equals')->setReturnType(Type::BOOL);
    $equals->addParameter('other')->setType('self');
    $equals->setBody('return $this->uuid->equals($other->uuid);');

    $factory = $class->addMethod('factory')->setReturnType('UuidFactory');
    $factory->setPrivate()->setStatic();
    $factory->setBody(<<<CODE
if (null === self::\$factory) {
    self::\$factory = new UuidFactory(new FeatureSet(true));
}

return self::\$factory;
CODE
);

    return \ImmMap($fqcn, $file);
}

const fromPhpValue = 'Fpp\Type\Guid\fromPhpValue';

function fromPhpValue(Guid $type, string $value): string
{
    return $type->classname() . '::fromString(' . $value . ')';
}

const toPhpValue = 'Fpp\Type\Guid\toPhpValue';

function toPhpValue(Guid $type, string $paramName): string
{
    return $paramName . '->toString()';
}

const validator = 'Fpp\Type\Guid\validator';

function validator(string $paramName): string
{
    return "\is_string(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Guid\validationErrorMessage';

function validationErrorMessage($paramName): string
{
    return "Error on \"$paramName\", string expected";
}

class Guid implements FppType
{
    use TypeTrait;
}
