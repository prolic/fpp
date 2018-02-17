<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class FunctionalConstructorDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $type = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\' . $definition->name()
            : '\\' . $definition->name();

        $code = "    const {$definition->name()} = '\\";
        $code .= "{$definition->namespace()}";

        if ($definition->namespace() !== '') {
            $code .= '\\' . "{$definition->name()}" . '\\' . "{$definition->name()}'" . ";\n\n";
        }

        $code .= "    function {$definition->name()}(";

        foreach ($definition->arguments() as $argument) {
            if (null !== $argument->typeHint()) {
                if ($argument->nullable()) {
                    $code .= '?';
                }
                if ($argument->namespace() === '') {
                    $ns = '\\' . $definition->namespace() . '\\';
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
