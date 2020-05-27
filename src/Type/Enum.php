<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Enum;

use function Fpp\assignment;
use function Fpp\buildDefaultPhpFile;
use function Fpp\char;
use Fpp\Configuration;
use function Fpp\constructorSeparator;
use Fpp\Definition;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\result;
use function Fpp\sepBy1list;
use function Fpp\spaces;
use function Fpp\spaces1;
use function Fpp\string;
use Fpp\Type as FppType;
use Fpp\Type\Int_\Int_;
use function Fpp\Type\Marker\markers;
use function Fpp\typeName;
use Fpp\TypeTrait;
use Nette\PhpGenerator\Type;
use Phunkie\Types\ImmList;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Tuple;

function definition(): Tuple
{
    return \Tuple(parse, build, fromPhpValue, toPhpValue, validator, validationErrorMessage);
}

const parse = 'Fpp\Type\Enum\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('enum')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result(Nil()))
        ),
        __($_)->_(assignment()),
        __($cs)->_(
            for_(
                __($constructors)->_(sepBy1list(typeName(), constructorSeparator())),
                __($_)->_(spaces()),
                __($_)->_(char(';'))
            )->call(
                fn ($c) => $c->map(
                    fn ($c) => new Constructor($c)
                ),
                $constructors
            )
        ),
    )->call(fn ($t, $ms, $cs) => new Enum($t, $ms, $cs), $t, $ms, $cs);
}

const build = 'Fpp\Type\Enum\build';

function build(Definition $definition, ImmMap $definitions, Configuration $config): ImmMap
{
    $type = $definition->type();

    if (! $type instanceof Enum) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Enum::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $classname = $type->classname();
    $lcClassName = \lcfirst($classname);

    $class = $file->addClass($fqcn)
        ->setFinal()
        ->setImplements($type->markers()->toArray());

    $options = [];
    $i = 0;
    $type->constructors()->map(function ($c) use ($class, &$options, &$i, $classname) {
        $class->addConstant($c->name(), $i)->setPublic();

        $options[] = $c->name();

        $method = $class->addMethod(\lcfirst($c->name()))->setPublic()->setStatic()->setReturnType('self');
        $method->setBody("return new self('{$c->name()}', $i);");

        ++$i;
    });

    $class->addConstant('Options', $options)->setPublic();

    $class->addProperty('name')->setType(Type::STRING)->setPrivate();
    $class->addProperty('value')->setType(Type::INT)->setPrivate();

    $constructor = $class->addMethod('__construct')->setPrivate();
    $constructor->addParameter('name')->setType(Type::STRING);
    $constructor->addParameter('value')->setType(Type::INT);
    $constructor->setBody("\$this->name = \$name;\n\$this->value = \$value;");

    $method = $class->addMethod('fromName')->setPublic()->setStatic()->setReturnType('self');
    $method->addParameter('name')->setType(Type::STRING);
    $method->setBody(<<<CODE
if (! isset(self::Options[\$name])) {
    throw new \InvalidArgumentException('Unknown enum name given');
}

return self::{\$name}();
CODE
    );

    $method = $class->addMethod('fromValue')->setPublic()->setStatic()->setReturnType('self');
    $method->addParameter('value')->setType(Type::INT);
    $method->setBody(<<<CODE
foreach (self::Options as \$n => \$v) {
    if (\$v === \$value) {
        return self::{\$n}();
    }
}

throw new \InvalidArgumentException('Unknown enum value given');
CODE
    );

    $method = $class->addMethod('equals')->setPublic()->setReturnType(Type::BOOL);
    $method->addParameter($lcClassName)->setType($classname);
    $method->setBody("\get_class(\$this) === \get_class(\${$lcClassName}) && \$this->name === \${$lcClassName}->name;");

    $method = $class->addMethod('name')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    $method = $class->addMethod('value')->setPublic()->setReturnType(Type::INT);
    $method->setBody('return $this->value;');

    $method = $class->addMethod('__toString')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    $method = $class->addMethod('toString')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    return \ImmMap($fqcn, $file);
}

const fromPhpValue = 'Fpp\Type\Enum\fromPhpValue';

function fromPhpValue(Enum $type, string $value): string
{
    return $type->classname() . '::fromName(' . $value . ')';
}

const toPhpValue = 'Fpp\Type\Enum\toPhpValue';

function toPhpValue(Int_ $type, string $paramName): string
{
    return $paramName . '->name()';
}

const validator = 'Fpp\Type\Enum\validator';

function validator(string $paramName): string
{
    return "\is_string(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Enum\validationErrorMessage';

function validationErrorMessage($paramName): string
{
    return "Error on \"$paramName\", string expected";
}

class Enum implements FppType
{
    use TypeTrait;

    /** @var Immlist<Constructor> */
    private ImmList $constructors;

    /** @param ImmList<Constructor> $constructors */
    public function __construct(string $classname, ImmList $markers, ImmList $constructors)
    {
        $this->classname = $classname;
        $this->markers = $markers;
        $this->constructors = $constructors;
    }

    /**
     * @return ImmList<Constructor>
     */
    public function constructors(): ImmList
    {
        return $this->constructors;
    }
}

class Constructor
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
