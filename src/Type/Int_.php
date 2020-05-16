<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Int_;

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

const parse = 'Fpp\Type\Int_\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('int')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t) => new Int_($t), $t);
}

const build = 'Fpp\Type\Int_\build';

function build(Int_ $type, ImmMap $builders): ClassType
{
    $class = new ClassType($type->classname());
    $class->setFinal(true);

    $class->addProperty('value')->setType(Type::INT)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::INT);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::INT);
    $method->setBody('return $this->value;');

    return $class;
}

const fromPhpValue = 'Fpp\Type\Int_\fromPhpValue';

function fromPhpValue(Int_ $type, int $value): string
{
    return 'new ' . $type->classname() . '(' . $value . ')';
}

const toPhpValue = 'Fpp\Type\Int_\toPhpValue';

function toPhpValue(Int_ $type, string $paramName): string
{
    return $paramName . '->value()';
}

class Int_ implements FppType
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
