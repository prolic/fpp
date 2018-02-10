<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class EnumDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $class = (new EnumClassDumper())->dump($definition);
        $constructors = (new FunctionalEnumConstructorsDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($constructors))->dump($definition);

        return $class . $functions;
    }
}
