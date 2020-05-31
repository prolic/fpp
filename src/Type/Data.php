<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Data;

use Fpp\Argument;
use function Fpp\assignment;
use function Fpp\buildDefaultPhpFile;
use function Fpp\calculateDefaultValue;
use function Fpp\char;
use Fpp\Configuration;
use function Fpp\constructorSeparator;
use Fpp\Definition;
use function Fpp\parseArguments;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\resolveType;
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
use Nette\PhpGenerator\PhpFile;
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

const parse = 'Fpp\Type\Data\parse';

function parse(): Parser
{
    return parseSimplified()->or(parseWitSubTypes());
}

const build = 'Fpp\Type\Data\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof Data) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Data::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setAbstract()
        ->setImplements($type->markers());

    $class->addMethod('fromArray')
        ->setStatic()
        ->setReturnType(Type::SELF)
        ->setAbstract()
        ->addParameter('data')
        ->setType(Type::ARRAY);

    $class->addMethod('toArray')
        ->setReturnType('array')
        ->setAbstract();

    $class->addMethod('equals')
        ->setReturnType(Type::BOOL)
        ->setAbstract()
        ->addParameter('other')
        ->setType(Type::SELF);

    $map = [$fqcn => $file];

    $constructors = $type->constructors();

    $singleConstructor = \count($constructors) === 1;

    foreach ($constructors as $constructor) {
        /** @var Constructor $constructor */
        if ($constructor->classname() === $type->classname() && ! $singleConstructor) {
            throw new \LogicException(\sprintf(
                'Invalid data type: "%s" has a subtype defined with the same name',
                $fqcn
            ));
        }

        $fqcn = $definition->namespace() . '\\' . $constructor->classname();
        $map[$fqcn] = buildSubType($definition, $constructor, $definitions, $config);
    }

    return $map;
}

const fromPhpValue = 'Fpp\Type\Data\fromPhpValue';

function fromPhpValue(Data $type, string $paramName): string
{
    return $type->classname() . '::fromArray(' . $paramName . ')';
}

const toPhpValue = 'Fpp\Type\Data\toPhpValue';

function toPhpValue(Data $type, string $paramName): string
{
    return $paramName . '->toArray()';
}

const validator = 'Fpp\Type\Data\validator';

function validator(string $paramName): string
{
    return "\is_array(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Data\validationErrorMessage';

function validationErrorMessage(string $paramName): string
{
    return "Error on \"$paramName\", array expected";
}

const equals = 'Fpp\Type\Data\equals';

function equals(string $paramName, string $otherParamName): string
{
    return "{$paramName}->equals($otherParamName)";
}

class Data implements FppType
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

    /** @return list<Constructor> */
    public function constructors(): array
    {
        return $this->constructors;
    }
}

class Constructor
{
    private string $classname;
    /** @var list<Argument> */
    private array $arguments;

    /** @param list<Argument> $arguments */
    public function __construct(string $classname, array $arguments)
    {
        $this->classname = $classname;
        $this->arguments = $arguments;
    }

    public function classname(): string
    {
        return $this->classname;
    }

    /** @return list<Argument> */
    public function arguments(): array
    {
        return $this->arguments;
    }
}

// helper functions for parse

function parseSimplified(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('data')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result([]))
        ),
        __($_)->_(assignment()),
        __($as)->_(parseArguments()),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms, $as) => new Data($t, $ms, [new Constructor($t, $as)]), $t, $ms, $as);
}

function parseWitSubTypes(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('data')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result([]))
        ),
        __($_)->_(assignment()),
        __($cs)->_(
            sepBy1list(
                for_(
                    __($c)->_(typeName()),
                    __($_)->_(spaces()),
                    __($as)->_(
                        parseArguments()
                    )
                )->call(fn ($c, $as) => new Constructor($c, $as), $c, $as),
                constructorSeparator()
            )
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(fn ($t, $ms, $cs) => new Data($t, $ms, $cs), $t, $ms, $cs);
}

// helper functions for build

function buildSubType(
    Definition $definition,
    Constructor $constr,
    array $definitions,
    Configuration $config
): PhpFile {
    $fqcn = $definition->namespace() . '\\' . $constr->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal();

    if ($definition->type()->classname() === $constr->classname()) {
        $class->setImplements($definition->type()->markers());
    } else {
        $class->setExtends($definition->type()->classname());
    }

    $constructor = $class->addMethod('__construct');

    $constructorBody = '';
    $fromArrayValidationBody = '';
    $fromArrayBody = "return new self(\n";
    $toArrayBody = "return [\n";
    $equalsBody = <<<CODE
if (\get_class(\$this) !== \get_class(\$other)) {
    return false;
}

CODE;

    \array_map(
        function (Argument $a) use (
            $class,
            $constructor,
            $definition,
            $definitions,
            $config,
            &$constructorBody,
            &$fromArrayValidationBody,
            &$fromArrayBody,
            &$toArrayBody,
            &$equalsBody
        ) {
            $property = $class->addProperty($a->name())->setPrivate()->setNullable($a->nullable());

            $resolvedType = resolveType($a->type(), $definition);
            $defaultValue = calculateDefaultValue($a);
            $fromArrayValidationBody .= calculateFromArrayValidationBodyFor($a, $resolvedType, $definitions, $config);
            $fromArrayBody .= calculateFromArrayBodyFor($a, $resolvedType, $definitions, $config);
            $toArrayBody .= calculateToArrayBodyFor($a, $resolvedType, $definitions, $config);
            $equalsBody .= equalsBodyFor($a, $resolvedType, $definitions, $config);

            if (null !== $defaultValue) {
                $param = $constructor->addParameter($a->name(), $defaultValue);
            } else {
                $param = $constructor->addParameter($a->name());
            }

            $param->setNullable($a->nullable());

            $constructorBody .= "\$this->{$a->name()} = \${$a->name()};\n";
            $method = $class->addMethod($a->name());
            $method->setBody("return \$this->{$a->name()};");

            if ($a->isList()) {
                $property->setType('array');
                $param->setType('array');

                if ($a->type()) {
                    $constructor->addComment('@param ' . $a->type() . '[] $' . $a->name());
                    $method->addComment('@return ' . $a->type() . '[]');
                }
                $method->setReturnType('array');
            } else {
                $property->setType($a->type());
                $param->setType($a->type());
                $method->setReturnType($a->type());
                $method->setReturnNullable($a->nullable());
            }

            if (null !== $a->type() && $a->isList()) {
                $property->setType('array');
                $property->addComment('@return ' . $a->type() . '[]');
            }
        },
        $constr->arguments()
    );

    $constructor->setBody(\substr($constructorBody, 0, -1));

    $fromArrayBody .= ');';
    $toArrayBody .= '];';

    $fromArray = $class->addMethod('fromArray')->setStatic()->setReturnType(Type::SELF);
    $fromArray->addParameter('data')->setType(Type::ARRAY);
    $fromArray->setBody($fromArrayValidationBody . $fromArrayBody);

    $toArray = $class->addMethod('toArray')->setReturnType(Type::ARRAY);
    $toArray->setBody($toArrayBody);

    $equals = $class->addMethod('equals')->setReturnType(Type::BOOL);
    $equals->addParameter('other')->setType($definition->type()->classname());
    $equals->setBody($equalsBody . <<<CODE

return true;

CODE);

    return $file;
}

function calculateFromArrayValidationBodyFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
{
    if ($a->isList()) {
        $code = "if (! isset(\$data['{$a->name()}']) || ! \is_array(\$data['{$a->name()}'])) {\n";
        $code .= "    throw new \InvalidArgumentException('Error on \"{$a->name()}\": array expected');\n";
        $code .= "}\n\n";

        return $code;
    }

    if ($a->nullable()) {
        $code = "if (isset(\$data['{$a->name()}']) && ! {%validator%}) {\n";
        $code .= "    throw new \InvalidArgumentException('{%validationErrorMessage%}');\n";
        $code .= "}\n\n";
    } else {
        $code = "if (! {%validator%}) {\n";
        $code .= "    throw new \InvalidArgumentException('{%validationErrorMessage%}');\n";
        $code .= "}\n\n";
    }

    switch ($a->type()) {
        case null:
            return '';
        case 'int':
            $validator = "\is_int(\$data['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\", int expected";
            break;
        case 'float':
            $validator = "\is_float(\$data['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\", float expected";
            break;
        case 'bool':
            $validator = "\is_bool(\$data['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\", bool expected";
            break;
        case 'string':
            $validator = "\is_string(\$data['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\", string expected";
            break;
        case 'array':
            $validator = "\is_array(\$data['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\", array expected";
            break;
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfig */
                $typeConfig = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfig) {
                    return '';
                }

                $validator = $typeConfig->validator()("data['{$a->name()}']");
                $validationErrorMessage = $typeConfig->validationErrorMessage()("\$data[\'{$a->name()}\']");
            } else {
                $type = $definition->type();

                $validator = $config->validatorFor($type)("data['{$a->name()}']");
                $validationErrorMessage = $config->validationErrorMessageFor($type)("\$data[\'{$a->name()}\']");
            }

            break;
    }

    return \str_replace(
        [
            '{%validator%}',
            '{%validationErrorMessage%}',
        ],
        [
            $validator,
            $validationErrorMessage,
        ],
        $code
    );
}

function calculateFromArrayBodyFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
{
    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            if ($a->nullable()) {
                return "    \$data['{$a->name()}'] ?? null,\n";
            }

            return "    \$data['{$a->name()}'],\n";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfig */
                $typeConfig = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfig) {
                    return "    \$data['{$a->name()}'],\n";
                }

                return '    ' . $typeConfig->fromPhpValue()("data['{$a->name()}']");
            }

            $builder = $config->fromPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn(\$e) => {$builder($definition->type(), '$e')}";

                return "    \array_map($callback, \$data['{$a->name()}']),\n";
            }

            if ($a->nullable()) {
                return "    isset(\$data['{$a->name()}']) ? " . $builder($definition->type(), "\$data['{$a->name()}']") . " : null,\n";
            }

            return '    ' . $builder($definition->type(), "\$data['{$a->name()}']") . ",\n";
    }
}

function calculateToArrayBodyFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
{
    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            return "    '{$a->name()}' => \$this->{$a->name()},\n";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfiguration */
                $typeConfiguration = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfiguration) {
                    return "    '{$a->name()}' => \$this->{$a->name()},\n";
                }

                return "    '{$a->name()}' => " . ($typeConfiguration->toPhpValue()('$this->' . $a->name())) . ",\n";
            }

            $builder = $config->toPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn({$a->type()} \$e) => {$builder($definition->type(), '$e')}";

                return "    '{$a->name()}' => \array_map($callback, \$this->{$a->name()}),\n";
            }

            return "    '{$a->name()}' => \$this->" . ($builder)($definition->type(), $a->name()) . ",\n";
    }
}

function equalsBodyFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
{
    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            return <<<CODE

if (\$this->{$a->name()} !== \$other->{$a->name()}) {
    return false;
}

CODE;
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfiguration */
                $typeConfiguration = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfiguration) {
                    return <<<CODE

if (\$this->{$a->name()} !== \$other->{$a->name()}) {
    return false;
}

CODE;
                }

                return <<<CODE

if (! {$typeConfiguration->equals()('$this->' . $a->name(), '$other->' . $a->name())}) {
    return false;
}

CODE;
            }

            $builder = $config->equalsFor($definition->type());

            if ($a->isList()) {
                return <<<CODE

if (\count(\$this->{$a->name()}) !== \count(\$other->{$a->name()})) {
    return false;
}

foreach (\$this->{$a->name()} as \$k => \$v) {
    if (! {$builder('$v', '$other->' . $a->name() . '[$k]')}) {
        return false;
    }
}

CODE;
            }

            return <<<CODE

if (! \$this->{$builder($a->name(), '$other->' . $a->name())}) {
    return false;
}

CODE;
    }
}
