<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class FunctionalAccessorsDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $type = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\' . $definition->name()
            : '\\' . $definition->name();

        $code = '';

        foreach ($definition->arguments() as $position => $argument) {
            $param = '$' . lcfirst($definition->name());

            $code .= "    const {$argument->name()} = '\\";
            $code .= "{$definition->namespace()}";

            if ($definition->namespace() !== '') {
                $code .= '\\' . "{$definition->name()}\\";
            }

            $code .= $argument->name() . "';\n\n";

            $returnType = '';
            if ($argument->typeHint()) {
                if ($argument->nullable()) {
                    $returnType = '?';
                }
                if ($argument->namespace() === '') {
                    $ns = '\\' . $definition->namespace() . '\\';
                } else {
                    $ns = $argument->namespace();
                }
                $returnType = ': ' . $returnType . $ns . $argument->typeHint();
            }

            $code .= <<<CODE
    function {$argument->name()}($type $param)$returnType {
        \$f = \Closure::bind(
            function ($type $param)$returnType {
                return $param->{$argument->name()};
            },
            null,
            $param
        );
    
        return \$f($param);
    }
    

CODE;
        }

        return $code;
    }
}
