<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Float_;

use function Fpp\char;
use Fpp\Parser;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use function Fpp\typeName;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Type;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Tuple;

function definition(): Tuple
{
    return \Tuple(parse, build, fromPhpValue, toPhpValue);
}

const parse = 'Fpp\Type\Float_\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('float')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t) => new Float_($t), $t);
}

const build = 'Fpp\Type\Float_\build';

function build(Float_ $type, ImmMap $builders): ClassType
{
    $class = new ClassType($type->classname());
    $class->setFinal(true);

    $class->addProperty('value')->setType(Type::FLOAT)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::FLOAT);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::FLOAT);
    $method->setBody('return $this->value;');

    return $class;
}

const fromPhpValue = 'Fpp\Type\Float_\fromPhpValue';

function fromPhpValue(Float_ $type, float $value): string
{
    return 'new ' . $type->classname() . '(' . $value . ')';
}

const toPhpValue = 'Fpp\Type\Float_\toPhpValue';

function toPhpValue(Float_ $type, string $paramName): string
{
    return $paramName . '->value()';
}

class Float_ implements FppType
{
    private string $classname;

    public function __construct(string $classname)
    {
        $this->classname = $classname;
    }

    public function classname(): string
    {
        return $this->classname;
    }
}
