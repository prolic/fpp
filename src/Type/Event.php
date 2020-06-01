<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp\Type\Event;

use Fpp\Argument;
use function Fpp\assignment;
use function Fpp\buildDefaultPhpFile;
use function Fpp\calculateDefaultValue;
use function Fpp\char;
use function Fpp\comma;
use Fpp\Configuration;
use function Fpp\constructorSeparator;
use Fpp\Definition;
use function Fpp\many1;
use function Fpp\not;
use function Fpp\parseArguments;
use Fpp\Parser;
use function Fpp\plus;
use function Fpp\renameDuplicateArgumentNames;
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

const parse = 'Fpp\Type\Event\parse';

function parse(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('event')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(spaces()),
        __($ms)->_(
            plus(markers(), result([]))
        ),
        __($_)->_(spaces()),
        __($_)->_(char('(')),
        __($_)->_(spaces()),
        __($eid)->_(typeName()),
        __($_)->_(comma()),
        __($aid)->_(typeName()),
        __($_)->_(spaces()),
        __($_)->_(char(')')),
        __($_)->_(assignment()),
        __($cs)->_(
            sepBy1list(
                for_(
                    __($c)->_(typeName()),
                    __($_)->_(spaces()),
                    __($en)->_(
                        for_(
                            __($_)->_(string('as')),
                            __($_)->_(spaces1()),
                            __($en)->_(many1(not(' '))),
                            __($_)->_(spaces1()),
                        )->yields($en)
                            ->or(result(''))
                    ),
                    __($as)->_(
                        parseArguments()->or(result([]))
                    )
                )->call(fn ($c, $en, $as) => new Constructor($c, $en, $as), $c, $en, $as),
                constructorSeparator()
            )
        ),
        __($_)->_(spaces()),
        __($_)->_(char(';'))
    )->call(
        fn ($t, $ms, $eid, $aid, $cs) => new Event($t, $ms, $eid, $aid, $cs),
        $t,
        $ms,
        $eid,
        $aid,
        $cs
    );
}

const build = 'Fpp\Type\Event\build';

function build(Definition $definition, array $definitions, Configuration $config): array
{
    $type = $definition->type();

    if (! $type instanceof Event) {
        throw new \InvalidArgumentException('Can only build definitions of ' . Event::class);
    }

    $fqcn = $definition->namespace() . '\\' . $type->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setAbstract()
        ->setImplements($type->markers());

    $class->addProperty('eventId')
        ->setProtected()
        ->setType($type->eventIdType());

    $class->addProperty('aggregateId')
        ->setProtected()
        ->setType($type->aggregateIdType());

    $constructor = $class->addMethod('__construct')
        ->setProtected();

    $constructor->addParameter('eventId')
        ->setType($type->eventIdType())
        ->setNullable();
    $constructor->addParameter('aggregateId')
        ->setType($type->aggregateIdType());
    $constructor->addParameter('payload')
        ->setType(Type::ARRAY);
    $constructor->addParameter('metadata')
        ->setType(Type::ARRAY)
        ->setDefaultValue([]);
    $constructor->setBody(<<<CODE
\$this->eventId = \$eventId ?? {$type->eventIdType()}::generate();
\$this->aggregateId = \$aggregateId;
\$this->payload = \$payload;
\$this->metadata = \$metadata;
CODE
    );

    $class->addMethod('eventType')
        ->setAbstract()
        ->setReturnType(Type::STRING);

    $class->addMethod('eventId')
        ->setBody('return $this->eventId;')
        ->setReturnType($type->eventIdType());

    $class->addMethod('aggregateId')
        ->setBody('return $this->aggregateId;')
        ->setReturnType($type->aggregateIdType());

    $class->addProperty('payload')
        ->setProtected()
        ->setType(Type::ARRAY);

    $class->addProperty('metadata')
        ->setProtected()
        ->setType(Type::ARRAY);

    $class->addMethod('payload')
        ->setReturnType(Type::ARRAY)
        ->setBody('return $this->payload;');

    $class->addMethod('metadata')
        ->setReturnType(Type::ARRAY)
        ->setBody('return $this->metadata;');

    $fromArrayMethod = $class->addMethod('fromArray')
        ->setStatic()
        ->setReturnType(Type::SELF);

    $fromArrayMethod->setReturnType(Type::SELF)
        ->addParameter('data')
        ->setType(Type::ARRAY);

    $fromArrayBody = "switch(\$data['event_type']) {\n";

    foreach ($type->constructors() as $constructor) {
        $fromArrayBody .= "    case '" . eventType($constructor, $definition->namespace()) . "':\n";
        $fromArrayBody .= "        \$classname = '{$constructor->classname()}';\n";
        $fromArrayBody .= "        break;\n";
    }

    $fromArrayBody .= <<<CODE
    default:
        throw new \InvalidArgumentException(
            'Unknown event type "' . \$data['event_type'] . '" given'
        );
}

return new \$classname(
    {$type->eventIdType()}::fromString(\$data['event_id']),
    {$type->aggregateIdType()}::fromString(\$data['aggregate_id']),
    \$data['payload'],
    \$data['metadata']
);

CODE;

    $fromArrayMethod->setBody($fromArrayBody);

    $class->addMethod('toArray')
        ->setReturnType('array')
        ->setBody(<<<CODE
return [
    'event_type' => \$this->eventType,
    'event_id' => \$this->eventId->toString(),
    'aggregate_id' => \$this->aggregateId->toString(),
    'payload' => \$this->payload,
    'metadata' => \$this->metadata,
];

CODE
    );

    $equalsMethod = $class->addMethod('equals')
        ->setReturnType(Type::BOOL);
    $equalsMethod->setBody(<<<CODE
if (\get_class(\$this) !== \get_class(\$other)) {
    return false;
}

return \$this->eventId->equals(\$other->eventId);

CODE
    );

    $equalsMethod->addParameter('other')
        ->setType(Type::SELF);

    $map = [$fqcn => $file];

    $constructors = $type->constructors();

    $singleConstructor = \count($constructors) === 1;

    foreach ($constructors as $constructor) {
        /** @var Constructor $constructor */
        if ($constructor->classname() === $type->classname() && ! $singleConstructor) {
            throw new \LogicException(\sprintf(
                'Invalid event type: "%s" has a subtype defined with the same name',
                $fqcn
            ));
        }

        $fqcn = $definition->namespace() . '\\' . $constructor->classname();
        $map[$fqcn] = buildSubType($definition, $constructor, $definitions, $config);
    }

    return $map;
}

const fromPhpValue = 'Fpp\Type\Event\fromPhpValue';

function fromPhpValue(Event $type, string $paramName): string
{
    return $type->classname() . '::fromArray(' . $paramName . ')';
}

const toPhpValue = 'Fpp\Type\Event\toPhpValue';

function toPhpValue(Event $type, string $paramName): string
{
    return $paramName . '->toArray()';
}

const validator = 'Fpp\Type\Event\validator';

function validator(string $paramName): string
{
    return "\is_array(\$$paramName)";
}

const validationErrorMessage = 'Fpp\Type\Event\validationErrorMessage';

function validationErrorMessage(string $paramName): string
{
    return "Error on \"$paramName\", array expected";
}

const equals = 'Fpp\Type\Event\equals';

function equals(string $paramName, string $otherParamName): string
{
    return "{$paramName}->equals($otherParamName)";
}

class Event implements FppType
{
    use TypeTrait;

    /** @var list<Constructor> */
    private array $constructors;

    private string $eventIdType;

    private string $aggregateIdType;

    /** @param list<Constructor> $constructors */
    public function __construct(
        string $classname,
        array $markers,
        string $eventIdType,
        string $aggregateIdType,
        array $constructors
    ) {
        $this->classname = $classname;
        $this->markers = $markers;
        $this->eventIdType = $eventIdType;
        $this->aggregateIdType = $aggregateIdType;
        $this->constructors = $constructors;
    }

    /** @return list<Constructor> */
    public function constructors(): array
    {
        return $this->constructors;
    }

    public function eventIdType(): string
    {
        return $this->eventIdType;
    }

    public function aggregateIdType(): string
    {
        return $this->aggregateIdType;
    }
}

class Constructor
{
    private string $classname;
    private string $eventType;
    /** @var list<Argument> */
    private array $arguments;

    /** @param list<Argument> $arguments */
    public function __construct(string $classname, string $eventType, array $arguments)
    {
        $this->classname = $classname;
        $this->eventType = $eventType;

        $this->arguments = renameDuplicateArgumentNames(
            [
                $eventType => 1,
            ],
            $arguments
        );
    }

    public function classname(): string
    {
        return $this->classname;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    /** @return list<Argument> */
    public function arguments(): array
    {
        return $this->arguments;
    }
}

// helper functions for build

function buildSubType(
    Definition $definition,
    Constructor $constructor,
    array $definitions,
    Configuration $config
): PhpFile {
    $fqcn = $definition->namespace() . '\\' . $constructor->classname();

    $file = buildDefaultPhpFile($definition, $config);

    $class = $file->addClass($fqcn)
        ->setFinal();

    if ($definition->type()->classname() === $constructor->classname()) {
        $class->setImplements($definition->type()->markers());
    } else {
        $class->setExtends($definition->type()->classname());
    }

    $class->addProperty('eventType')
        ->setType(Type::STRING)
        ->setPrivate()
        ->setValue(eventType($constructor, $definition->namespace()));

    $class->addMethod('eventType')
        ->setReturnType(Type::STRING)
        ->setBody('return $this->eventType;');

    $occur = $class->addMethod('occur')
        ->setStatic()
        ->setReturnType(Type::SELF);

    /** @var Event $event */
    $event = $definition->type();

    $occur->addParameter('eventId')
        ->setType($event->aggregateIdType());

    $occurBody = <<<CODE
\$_event = new self(
    null,
    \$eventId,
    [

CODE;
    $occurBody2 = '';

    \array_map(
        function (Argument $a) use (
            $class,
            $occur,
            $definition,
            $definitions,
            $config,
            &$occurBody,
            &$occurBody2
        ) {
            $property = $class->addProperty($a->name())->setPrivate()->setNullable()->setInitialized();

            $resolvedType = resolveType($a->type(), $definition);
            $defaultValue = calculateDefaultValue($a);
            $fromPhpValue = calculateFromPhpValueFor($a, $resolvedType, $definitions, $config);
            $toPhpValue = calculateToPhpValueFor($a, $resolvedType, $definitions, $config);

            if (null !== $defaultValue) {
                $param = $occur->addParameter($a->name(), $defaultValue);
            } else {
                $param = $occur->addParameter($a->name());
            }

            $param->setNullable($a->nullable());

            $occurBody .= "    $toPhpValue";
            $occurBody2 .= "\$_event->{$a->name()} = \${$a->name()};\n";
            $method = $class->addMethod($a->name());
            $method->setBody(<<<CODE
if (null === \$this->{$a->name()}) {
    \$this->{$a->name()} = $fromPhpValue;
}

return \$this->{$a->name()};

CODE
);

            if ($a->isList()) {
                $property->setType('array');
                $param->setType('array');

                if ($a->type()) {
                    $occur->addComment('@param ' . $a->type() . '[] $' . $a->name());
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
        $constructor->arguments()
    );

    $occurBody .= "    ]\n);\n";

    if ($occurBody2 !== '') {
        $occurBody .= "\n$occurBody2";
    }

    $occurBody .= "\nreturn \$_event;";

    $occur->setBody($occurBody);

    return $file;
}

function calculateFromPhpValueFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
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
                return "\$this->payload['{$a->name()}'] ?? null";
            }

            return "\$this->payload['{$a->name()}']";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfig */
                $typeConfig = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfig) {
                    return "\$this->payload['{$a->name()}']";
                }

                return $typeConfig->fromPhpValue()("data['{$a->name()}']");
            }

            $builder = $config->fromPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn(\$e) => {$builder($definition->type(), '$e')}";

                return "    \array_map($callback, \$this->payload['{$a->name()}'])";
            }

            if ($a->nullable()) {
                return "isset(\$this->payload['{$a->name()}']) ? " . $builder($definition->type(), "\$this->payload['{$a->name()}']") . ' : null';
            }

            return $builder($definition->type(), "\$this->payload['{$a->name()}']") . '';
    }
}

function calculateToPhpValueFor(Argument $a, ?string $resolvedType, array $definitions, Configuration $config): string
{
    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            return "    '{$a->name()}' => \${$a->name()},\n";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfiguration */
                $typeConfiguration = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfiguration) {
                    return "    '{$a->name()}' => \${$a->name()},\n";
                }

                return "    '{$a->name()}' => " . ($typeConfiguration->toPhpValue()('$' . $a->name())) . ",\n";
            }

            $builder = $config->toPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn({$a->type()} \$e) => {$builder($definition->type(), '$e')}";

                return "    '{$a->name()}' => \array_map($callback, \${$a->name()}),\n";
            }

            return "    '{$a->name()}' => \$" . ($builder)($definition->type(), $a->name()) . ",\n";
    }
}

function eventType(Constructor $constructor, string $namespace): string
{
    return empty($constructor->eventType())
        ? $namespace . '\\' . $constructor->classname()
        : $constructor->eventType();
}
