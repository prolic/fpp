<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class FunctionalSettersDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $type = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\' . $definition->name()
            : '\\' . $definition->name();

        $code = '';

        foreach ($definition->arguments() as $position => $argument) {
            $functionName = 'set' . ucfirst($argument->name());
            $param = '$' . lcfirst($definition->name());
            $code .= "    const $functionName = '\\";
            $code .= "{$definition->namespace()}";

            if ($definition->namespace() !== '') {
                $code .= '\\' . "{$definition->name()}\\";
            }

            $code .= $functionName . "';\n\n";
            $code .= <<<CODE
    function $functionName($type $param, {$argument->typehint()} \${$argument->name()}): $type {
        \$f = \Closure::bind(
            function ($type $param, \$value) {
                return $param->{\$value};
            },
            null,
            $param
        );
    
        return new $type(
CODE;

            foreach ($definition->arguments() as $innerPosition => $innerArgument) {
                if ($innerPosition === $position) {
                    $code .= "\${$innerArgument->name()}, ";
                } else {
                    $code .= "\$f($param, '{$innerArgument->name()}'), ";
                }
            }

            $code = substr($code, 0, -2) . ");\n    }\n\n";
        }

        return $code;
    }
}
