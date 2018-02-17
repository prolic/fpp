<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Argument;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;

class DataClassDumper implements Dumper
{
    /**
     * @var DefinitionCollection
     */
    private $definitionCollection;

    public function __construct(DefinitionCollection $collection)
    {
        $this->definitionCollection = $collection;
    }

    public function dump(Definition $definition): string
    {
        $code = '';
        $indent = '';

        if ($definition->namespace() !== '') {
            $code = "namespace {$definition->namespace()} {\n    ";
            $indent = '    ';
        }

        $code .= "final class {$definition->name()}\n$indent{\n";

        foreach ($definition->arguments() as $argument) {
            $code .= "$indent    private \${$argument->name()};\n";
        }

        if (! empty($definition->arguments())) {
            $code .= "\n";
        }

        $code .= "$indent    public function __construct(";

        foreach ($definition->arguments() as $argument) {
            if ($argument->nullable()) {
                $code .= '?';
            }
            if ($argument->namespace() && substr($argument->namespace(), 0, 1) !== '\\') {
                $ns = '\\' . $definition->namespace() . '\\' . $argument->namespace();
            } else {
                $ns = $argument->namespace();
            }
            $code .= "$ns{$argument->typeHint()} \${$argument->name()}, ";
        }

        if (! empty($definition->arguments())) {
            $code = substr($code, 0, -2);
        }

        $code .= ")\n$indent    {\n";

        foreach ($definition->arguments() as $argument) {
            $code .= "$indent        \$this->{$argument->name()} = \${$argument->name()};\n";
        }

        $code .= "$indent    }\n";

        foreach ($definition->derivings() as $deriving) {
            switch ((string) $deriving) {
                case Deriving\ToString::VALUE:
                    $argument = current($definition->arguments());
                    $code .= <<<CODE
    
$indent    public function __toString(): string
$indent    {
$indent        return (string) \$this->{$argument->name()};
$indent    }

CODE;

                    break;
                case Deriving\ToArray::VALUE:
                    $code .= <<<CODE
    
$indent    public function toArray(): array
$indent    {
$indent        return [

CODE;

                    foreach ($definition->arguments() as $argument) {
                        $return = "\$this->{$argument->name()},\n";
                        $argumentNamespace = substr($argument->namespace(), 0, 1) === '\\'
                            ? substr($argument->namespace(), 1, -1)
                            : ($argument->namespace() === ''
                                ? $definition->namespace()
                                : $definition->namespace() . '\\' . $argument->namespace());

                        if ($argument->isScalartypeHint()) {
                            // ignore
                        } elseif ($this->definitionCollection->hasDefinition($argumentNamespace, $argument->typeHint())) {
                            $argumentDefinition = $this->definitionCollection->definition($argumentNamespace, $argument->typeHint());

                            if (in_array(new Deriving\ToArray(), $argumentDefinition->derivings())) {
                                $return = "\$this->{$argument->name()}->toArray(),\n";
                            } elseif (in_array(new Deriving\ToString(), $argumentDefinition->derivings())) {
                                $return = "\$this->{$argument->name()}->__toString(),\n";
                            } elseif (in_array(new Deriving\ToScalar(), $argumentDefinition->derivings())) {
                                $return = "\$this->{$argument->name()}->toScalar(),\n";
                            }
                        } elseif (class_exists($argumentNamespace . '\\' . $argument->typeHint())) {
                            $reflectionClass = new \ReflectionClass($argumentNamespace . '\\' . $argument->typeHint());

                            if ($reflectionClass->hasMethod('toArray')) {
                                $method = $reflectionClass->getMethod('toArray');
                                if ($method->isPublic()) {
                                    $return = "\$this->{$argument->name()}->toArray(),\n";
                                }
                            } elseif ($reflectionClass->hasMethod('__toString')) {
                                $method = $reflectionClass->getMethod('__toString');
                                if ($method->isPublic()) {
                                    $return = "\$this->{$argument->name()}->__toString(),\n";
                                }
                            } elseif ($reflectionClass->hasMethod('toString')) {
                                $method = $reflectionClass->getMethod('toString');
                                if ($method->isPublic()) {
                                    $return = "\$this->{$argument->name()}->toString(),\n";
                                }
                            } elseif ($reflectionClass->hasMethod('toScalar')) {
                                $method = $reflectionClass->getMethod('toScalar');
                                if ($method->isPublic()) {
                                    $return = "\$this->{$argument->name()}->toScalar(),\n";
                                }
                            }
                        }

                        $code .= "$indent            '{$argument->name()}' => $return";
                    }

                    $code .= <<<CODE
$indent        ];
$indent    }

$indent    public static function fromArray(array \$data): {$definition->name()}
$indent    {

CODE;
                    $constructorParams = '';
                    foreach ($definition->arguments() as $argument) {
                        $code .= <<<CODE
$indent        if (!isset(\$data['{$argument->name()}'])) {
$indent            throw new \InvalidArgumentException(
$indent                'Key {$argument->name()} is missing in \$data'
$indent            );
$indent        }


CODE;
                        $param = '$data[\'' . $argument->name() . '\'], ';
                        $argumentNamespace = substr($argument->namespace(), 0, 1) === '\\'
                            ? substr($argument->namespace(), 1, -1)
                            : ($argument->namespace() === ''
                                ? $definition->namespace()
                                : $definition->namespace() . '\\' . $argument->namespace());
                        $class = $argument->namespace() . $argument->typeHint();

                        if ($argument->isScalartypeHint()) {
                            // ignore
                        } elseif ($this->definitionCollection->hasDefinition($argumentNamespace, $argument->typeHint())) {
                            $argumentDefinition = $this->definitionCollection->definition($argumentNamespace, $argument->typeHint());

                            if (in_array(new Deriving\ToArray(), $argumentDefinition->derivings())) {
                                $param = "$class::fromArray(\$data['{$argument->name()}']), ";
                            } elseif (in_array(new Deriving\ToString(), $argumentDefinition->derivings())) {
                                $param = "new $class(\$data['{$argument->name()}']), ";
                            } elseif (in_array(new Deriving\ToScalar(), $argumentDefinition->derivings())) {
                                $param = "$class::fromScalar(\$data['{$argument->name()}']), ";
                            }
                        } elseif (class_exists($argumentNamespace . '\\' . $argument->typeHint())) {
                            $reflectionClass = new \ReflectionClass($argumentNamespace . '\\' . $argument->typeHint());

                            if ($reflectionClass->hasMethod('fromArray')) {
                                $method = $reflectionClass->getMethod('fromArray');
                                if ($method->isPublic() && $method->isStatic()) {
                                    $param = "\\$class::fromArray(\$data['{$argument->name()}']), ";
                                }
                            } elseif ($reflectionClass->hasMethod('fromScalar')) {
                                $method = $reflectionClass->getMethod('fromScalar');
                                if ($method->isPublic() && $method->isStatic()) {
                                    $param = "\\$class::fromScalar(\$data['{$argument->name()}']), ";
                                }
                            } elseif ($reflectionClass->hasMethod('fromString')) {
                                $method = $reflectionClass->getMethod('fromString');
                                if ($method->isPublic() && $method->isStatic()) {
                                    $param = "\\$class::fromString(\$data['{$argument->name()}']), ";
                                }
                            } else {
                                $constructor = $reflectionClass->getConstructor();
                                if ($constructor->isPublic() && $constructor->getNumberOfParameters() === 1) {
                                    $parameters = $constructor->getParameters();
                                    $parameter = current($parameters);
                                    /* @var \ReflectionParameter $parameter */
                                    if ($type = $parameter->getType()) {
                                        switch ($type->getName()) {
                                            case 'string':
                                            case 'int':
                                            case 'bool':
                                            case 'float':
                                            case 'array':
                                                $param = "new \\$class(\$data['{$argument->name()}']), ";
                                                break;
                                        }
                                    }
                                }
                            }
                        }

                        $constructorParams .= $param;
                    }

                    $code .= "$indent        return new {$definition->name()}("
                        . substr($constructorParams, 0, -2)
                        . ");\n$indent    }\n";
                    break;
                case Deriving\ToScalar::VALUE:
                    $argument = current($definition->arguments());
                    /* @var Argument $argument */
                    $type = $argument->typeHint();
                    if ($argument->nullable()) {
                        $type = '?' . $type;
                    }
                    $code .= <<<CODE

$indent    public function toScalar(): $type
$indent    {
$indent        return \$this->{$argument->name()};
$indent    }

$indent    public static function fromScalar($type \${$argument->name()}): {$definition->name()}
$indent    {
$indent        return new {$definition->name()}(\${$argument->name()});
$indent    }

CODE;
                    break;
                case Deriving\Equals::VALUE:
                    $fqcn = '\\' . $definition->name();

                    if ('' !== $definition->namespace()) {
                        $fqcn = '\\' . $definition->namespace() . $fqcn;
                    }

                    $code .= <<<CODE
    
$indent    public function equals($fqcn \$other): bool
$indent    {
$indent        return 
CODE;

                    foreach ($definition->arguments() as $argument) {
                        $code .= "\$this->{$argument->name()} === \$other->{$argument->name()}\n$indent            && ";
                    }

                    $code = substr($code, 0, -(strlen($indent) + 16)) . ";\n$indent    }\n";

                    break;
            }
        }

        $code .= "$indent}";

        if ($definition->namespace() !== '') {
            $code .= "\n}";
        }

        $code .= "\n\n";

        return $code;
    }
}
