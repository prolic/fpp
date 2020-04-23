<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Fpp\Type\BoolType;
use Fpp\Type\Data\Argument;
use Fpp\Type\DataType;
use Fpp\Type\EnumType;
use Fpp\Type\FloatType;
use Fpp\Type\IntType;
use Fpp\Type\StringType;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Type;
use Phunkie\Types\ImmMap;

const buildEnum = 'Fpp\buildEnum';

function buildEnum(EnumType $enum, ImmMap $builders): ClassType
{
    $classname = $enum->classname();
    $lcClassName = \lcfirst($classname);

    $class = new ClassType($classname);
    $class->setFinal(true);

    $options = [];
    $i = 0;
    $enum->constructors()->map(function ($c) use ($class, &$options, &$i, $classname) {
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

    return $class;
}

const buildData = 'Fpp\buildData';

function buildData(DataType $data, ImmMap $builders): ClassType
{
    $classname = $data->classname();

    $class = new ClassType($classname);
    $class->setFinal(true);

    $constructor = $class->addMethod('__construct');

    $body = '';
    $data->arguments()->map(function (Argument $a) use ($data, $class, $constructor, &$body) {
        $property = $class->addProperty($a->name())->setPrivate()->setNullable($a->nullable());

        switch ($a->type()) {
            case 'int':
                $defaultValue = (int) $a->defaultValue();
                break;
            case 'float':
                $defaultValue = (float) $a->defaultValue();
                break;
            case 'bool':
                $defaultValue = ('true' === $a->defaultValue());
                break;
            default:
                $defaultValue = $a->defaultValue() === '[]' ? [] : $a->defaultValue();
                break;
        }

        $param = $constructor->addParameter($a->name(), $defaultValue);

        $body .= "\$this->{$a->name()} = \${$a->name()};\n";
        $method = $class->addMethod($a->name());
        $method->setBody("return \$this->{$a->name()};");

        if ($a->isList()) {
            $property->setType('array');
            $param->setType('array');

            if ($a->type()) {
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
    });

    $constructor->setBody(\substr($body, 0, -1));

    return $class;
}

const buildString = 'Fpp\buildString';

function buildString(StringType $type, ImmMap $builders): ClassType
{
    $class = new ClassType($type->classname());
    $class->setFinal(true);

    $class->addProperty('value')->setType(Type::STRING)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::STRING);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::STRING);
    $method->setBody('return $this->value;');

    return $class;
}

const buildInt = 'Fpp\buildInt';

function buildInt(IntType $type, ImmMap $builders): ClassType
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

const buildFloat = 'Fpp\buildFloat';

function buildFloat(FloatType $type, ImmMap $builders): ClassType
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

const buildBool = 'Fpp\buildBool';

function buildBool(BoolType $type, ImmMap $builders): ClassType
{
    $class = new ClassType($type->classname());
    $class->setFinal(true);

    $class->addProperty('value')->setType(Type::BOOL)->setPrivate();

    $constructor = $class->addMethod('__construct');
    $constructor->addParameter('value')->setType(Type::BOOL);
    $constructor->setBody('$this->value = $value;');

    $method = $class->addMethod('value')->setReturnType(Type::BOOL);
    $method->setBody('return $this->value;');

    return $class;
}
