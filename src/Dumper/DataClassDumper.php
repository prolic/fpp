<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;
use Fpp\Deriving;

final class DataClassDumper implements Dumper
{
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
            $code .= "{$argument->typeHint()} \${$argument->name()}, ";
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
                case Deriving\Show::VALUE:
                    break;
                case Deriving\StringConverter::VALUE:
                    $argument = current($definition->arguments());
                    $code .= <<<CODE
    
$indent    public function __toString(): string
$indent    {
$indent        return (string) \$this->{$argument->name()};
$indent    }

CODE;

                    break;
                case Deriving\ArrayConverter::VALUE:
                    $code .= <<<CODE
    
$indent    public function toArray(): array
$indent    {
$indent        return [

CODE;

                    foreach ($definition->arguments() as $argument) {
                        $code .= "$indent            '{$argument->name()}' => \$this->{$argument->name()},\n";
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
                        $constructorParams .= '$data[\'' . $argument->name() . '\'], ';
                    }

                    $code .= "$indent        return new {$definition->name()}("
                        . substr($constructorParams, 0, -2)
                        . ");\n$indent    }\n";
                    break;
                case Deriving\ScalarConverter::VALUE:
                    $argument = current($definition->arguments());
                    $code .= <<<CODE

$indent    public function toScalar(): {$argument}
$indent    {
$indent        return [

CODE;

                    foreach ($definition->arguments() as $argument) {
                        $code .= "$indent            '{$argument->name()}' => \$this->{$argument->name()},\n";
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
                        $constructorParams .= '$data[\'' . $argument->name() . '\'], ';
                    }

                    $code .= "$indent        return new {$definition->name()}("
                        . substr($constructorParams, 0, -2)
                        . ");\n$indent    }\n";
                    break;
                case Deriving\ValueObject::VALUE:
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
