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
            plus(markers(), result([]))
        ),
        __($_)->_(assignment()),
        __($cs)->_(
            for_(
                __($constructors)->_(sepBy1list(typeName(), constructorSeparator())),
                __($_)->_(spaces()),
                __($_)->_(char(';'))
            )->call(
                fn ($c) => \array_map(
                    fn ($c) => new Constructor($c),
                    $c
                ),
                $constructors
            )
        ),
    )->call(fn ($t, $ms, $cs) => new Enum($t, $ms, $cs), $t, $ms, $cs);
}

const build = 'Fpp\Type\Enum\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof Enum) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Enum::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal()
        ->setImplements($type->markers());

    $options = [];
    $i = 0;

    \array_map(
        function ($c) use ($class, &$options, &$i) {
            $class->addConstant($c->name(), $i)->setPublic();

            $options[] = $c->name();

            $method = $class->addMethod(\lcfirst($c->name()))->setPublic()->setStatic()->setReturnType('self');
            $method->setBody("return new self('{$c->name()}', $i);");

            ++$i;
        },
        $type->constructors()
    );

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
foreach (self::Options as \$i => \$n) {
    if (\$n === \$name) {
        return new self(\$n, \$i);
    }
}

throw new \InvalidArgumentException('Unknown enum name given');
CODE
    );

    $method = $class->addMethod('fromValue')->setPublic()->setStatic()->setReturnType('self');
    $method->addParameter('value')->setType(Type::INT);
    $method->setBody(<<<CODE
if (! isset(self::Options[\$value])) {
    throw new \InvalidArgumentException('Unknown enum value given');
}

return new self(self::Options[\$value], \$value);
CODE
    );

    $method = $class->addMethod('equals')->setPublic()->setReturnType(Type::BOOL);
    $method->addParameter('other')->setType(Type::SELF);
    $method->setBody('return $this->name === $other->name;');

    $method = $class->addMethod('name')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    $method = $class->addMethod('value')->setPublic()->setReturnType(Type::INT);
    $method->setBody('return $this->value;');

    $method = $class->addMethod('__toString')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    $method = $class->addMethod('toString')->setPublic()->setReturnType(Type::STRING);
    $method->setBody('return $this->name;');

    return [$fqcn => $file];
}

const fromPhpValue = 'Fpp\Type\Enum\fromPhpValue';

function fromPhpValue(Enum $type, string $paramName): string
{
    return $type->classname() . '::fromName(' . $paramName . ')';
}

const toPhpValue = 'Fpp\Type\Enum\toPhpValue';

function toPhpValue(Enum $type, string $paramName): string
{
    return $paramName . '->name()';
}

const validator = 'Fpp\Type\Enum\validator';

function validator(string $paramName): string
{
    return "\is_string(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Enum\validationErrorMessage';

function validationErrorMessage(string $paramName): string
{
    return "Error on \"$paramName\", string expected";
}

const equals = 'Fpp\Type\Enum\equals';

function equals(string $paramName, string $otherParamName): string
{
    return "{$paramName}->equals($otherParamName)";
}

class Enum implements FppType
{
    use TypeTrait;

    /** @var list<Constructor> */
    private array $constructors;

    /** @param list<Constructor> $constructors */
    public function __construct(string $classname, array $markers, array $constructors)
    {
        $this->classname = $classname;
        $this->markers = $markers;
        $this->constructors = $constructors;
    }

    /**
     * @return list<Constructor>
     */
    public function constructors(): array
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
