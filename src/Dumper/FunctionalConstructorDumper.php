<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class FunctionalConstructorDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $type = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\' . $definition->name()
            : '\\' . $definition->name();

        $code = "    const {$definition->name()} = '\\";
        $code .= "{$definition->namespace()}";

        if ($definition->namespace() !== '') {
            $code .= '\\' . "{$definition->name()}';\n\n";
        }

        $code .= "    function {$definition->name()}(";

        foreach ($definition->arguments() as $argument) {
            if (null !== $argument->typehint()) {
                $code .= "{$argument->typehint()} ";
            }

            $code .= "\${$argument->name()}, ";
        }

        if (! empty($definition->arguments())) {
            $code = substr($code, 0, -2);
        }

        $code .= "): $type ";
        $code .= "{\n        return new $type(";

        foreach ($definition->arguments() as $argument) {
            $code .= "\${$argument->name()}, ";
        }

        if (! empty($definition->arguments())) {
            $code = substr($code, 0, -2);
        }

        $code .= ");\n    }\n\n";

        return $code;
    }
}
