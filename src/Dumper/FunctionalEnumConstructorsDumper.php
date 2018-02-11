<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class FunctionalEnumConstructorsDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $prefix = $definition->namespace() !== ''
            ? '\\' . $definition->namespace() . '\\'
            : '\\';

        $code = '';
        foreach ($definition->arguments() as $argument) {
            $ns = $argument->namespace() ? $argument->namespace() . '\\' : '';
            $code .= "    const {$argument->name()} = '$prefix{$definition->name()}\\";
            $code .= <<<CODE
{$argument->name()}';

    function {$argument->name()}()
    {
        return new $prefix{$ns}{$argument->name()}();
    }


CODE;
        }

        return $code;
    }
}
