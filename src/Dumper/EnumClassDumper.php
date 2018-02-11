<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class EnumClassDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $code = '';
        $indent = '';

        if ($definition->namespace() !== '') {
            $code = "namespace {$definition->namespace()} {\n    ";
            $indent = '    ';
        }

        $code .= "abstract class {$definition->name()}\n$indent{\n";
        $code .= "$indent    const OPTIONS = [\n";

        foreach ($definition->arguments() as $argument) {
            $ns = $argument->namespace() ? $argument->namespace() . '\\' : '';
            $code .= "$indent        $ns{$argument->name()}::class,\n";
        }

        $code .= <<<CODE
$indent    ];

$indent    const OPTION_VALUES = [

CODE;

        foreach ($definition->arguments() as $argument) {
            $code .= "$indent        '{$argument->name()}',\n";
        }

        $code .= <<<CODE
$indent    ];

$indent    final public function __construct()
$indent    {
$indent        \$valid = false;

$indent        foreach(self::OPTIONS as \$value) {
$indent            if (\$this instanceof \$value) {
$indent                \$valid = true;
$indent                break;
$indent            }
$indent        }

$indent        if (! \$valid) {
$indent            \$self = get_class(\$this);
$indent            throw new \LogicException("Invalid {$definition->name()} '\$self' given");
$indent        }
$indent    }

$indent    public function equals({$definition->name()} \$other): bool
$indent    {
$indent        return get_class(\$this) === get_class(\$other);
$indent    }

$indent    public function __toString(): string
$indent    {
$indent        return static::VALUE;
$indent    }
$indent}

CODE;

        foreach ($definition->arguments() as $argument) {
            $dns = $definition->namespace() ? '\\' . $definition->namespace() . '\\' : '';
            if ($argument->namespace()) {
                $ns = substr($argument->namespace(), 0, 1) === '//'
                    ? $argument->namespace()
                    : $definition->namespace() . '\\' . $argument->namespace();
                $code .= "}\n\nnamespace $ns {\n";
            }
            $code .= "\n$indent" . <<<CODE
final class {$argument->name()} extends $dns{$definition->name()}
$indent{
$indent    const VALUE = '{$argument->name()}';
$indent}

CODE;
        }

        if (! $definition->namespace() !== '') {
            $code .= "}\n";
        }

        $code .= "\n";

        return $code;
    }
}
