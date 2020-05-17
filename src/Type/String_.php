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

use function Fpp\char;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\result;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use function Fpp\Type\Marker\markers;
use function Fpp\typeName;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Type;
use Phunkie\Types\ImmList;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Tuple;

function definition(): Tuple
{
    return \Tuple(parse, build, fromPhpValue, toPhpValue);
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
            plus(markers(), result(Nil()))
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms) => new String_($t, $ms), $t, $ms);
}

const build = 'Fpp\Type\String_\build';

function build(String_ $type, ImmMap $builders): ClassType
{
    $class = new ClassType($type->classname());
    $class->setFinal(true);
    $class->setImplements($type->markers()->toArray());

    $class->addProperty('value')->setType(Type::STRING)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::STRING);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::STRING);
    $method->setBody('return $this->value;');

    return $class;
}

const fromPhpValue = 'Fpp\Type\String_\fromPhpValue';

function fromPhpValue(String_ $type, string $value): string
{
    return 'new ' . $type->classname() . '(' . $value . ')';
}

const toPhpValue = 'Fpp\Type\String_\toPhpValue';

function toPhpValue(String_ $type, string $paramName): string
{
    return $paramName . '->value()';
}

class String_ implements FppType
{
    private string $classname;
    private ImmList $markers;

    public function __construct(string $classname, ImmList $markers)
    {
        $this->classname = $classname;
        $this->markers = $markers;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    public function markers(): ImmList
    {
        return $this->markers;
    }
}
