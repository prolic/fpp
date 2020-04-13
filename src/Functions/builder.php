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

use Fpp\Type\EnumType;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Type;

const buildEnum = 'Fpp\buildEnum';

function buildEnum(EnumType $enum): ClassType
{
    $className = $enum->classname();
    $lcClassName = \lcfirst($className);

    $class = new ClassType($className);

    $options = [];
    foreach ($enum->constructors() as $key => $constructor) {
        $options[$key] = $constructor->name();
    }

    $class->addConstant('Options', $options)->setPublic();

    foreach ($enum->constructors() as $key => $constructor) {
        $class->addConstant($constructor->name(), $key)->setPublic();
    }

    $class->addProperty('name')->setType(Type::STRING)->setPrivate();
    $class->addProperty('value')->setType(Type::INT)->setPrivate();

    $constructor = $class->addMethod('__construct')->setPrivate();
    $constructor->addParameter('name')->setType(Type::STRING);
    $constructor->addParameter('value')->setType(Type::INT);
    $constructor->setBody("\$this->name = \$name;\n\$this->value = \$value;");

    foreach ($enum->constructors() as $key => $constructor) {
        $method = $class->addMethod(\lcfirst($constructor->name()))->setPublic()->setStatic()->setReturnType($className);
        $method->setBody("return new self('{$constructor->name()}', $key);");
    }

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
foreach (self::OPTIONS as \$n => \$v) {
    if (\$v === \$value) {
        return self::{\$n}();
    }
}

throw new \InvalidArgumentException('Unknown enum value given');
CODE
    );

    $method = $class->addMethod('equals')->setPublic()->setReturnType(Type::BOOL);
    $method->addParameter($lcClassName)->setType($className);
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
