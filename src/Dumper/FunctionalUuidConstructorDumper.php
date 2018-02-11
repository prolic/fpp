<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class FunctionalUuidConstructorDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $type = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\' . $definition->name()
            : '\\' . $definition->name();

        $variableName = lcfirst($definition->name());

        $code = "    const {$definition->name()} = '\\";
        $code .= "{$definition->namespace()}";

        if ($definition->namespace() !== '') {
            $code .= '\\' . "{$definition->name()}';\n\n";
        }

        $code .= <<<CODE
    function generate(): $type {
        return $type::generate();
    }

    function fromString(string $$variableName): $type {
        return $type::fromString(\$$variableName);
    }


CODE;

        return $code;
    }
}
