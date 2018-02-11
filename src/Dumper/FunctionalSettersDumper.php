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

            $typeHint = '';
            if ($argument->typeHint()) {
                if ($argument->nullable()) {
                    $typeHint = '?';
                }
                if ($argument->namespace() && substr($argument->namespace(), 0, 1) !== '\\') {
                    $ns = '\\' . $definition->namespace() . '\\' . $argument->namespace();
                } else {
                    $ns = $argument->namespace();
                }
                $typeHint .= $ns . $argument->typeHint();
            }

            $code .= <<<CODE
$functionName';

    function $functionName($type $param, $typeHint \${$argument->name()}): $type {

CODE;

            if (count($definition->arguments()) > 1) {
                $code .= <<<CODE
        \$f = \Closure::bind(
            function ($type $param, \$value) {
                return $param->{\$value};
            },
            null,
            $param
        );


CODE;
            }

            $code .= <<<CODE
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
