<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class FunctionalAggregateChangedConstructorDumper implements Dumper
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
            if (null !== $argument->typeHint()) {
                if ($argument->nullable()) {
                    $code .= '?';
                }
                if ($argument->namespace() && substr($argument->namespace(), 0, 1) !== '\\') {
                    $ns = '\\' . $definition->namespace() . '\\' . $argument->namespace();
                } else {
                    $ns = $argument->namespace();
                }
                $code .= "$ns{$argument->typeHint()} ";
            }

            $code .= "\${$argument->name()}, ";
        }

        if (! empty($definition->arguments())) {
            $code = substr($code, 0, -2);
        }

        $code .= "): $type ";
        $code .= "{\n        return $type::withData(";

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
