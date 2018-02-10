<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class DataDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $class = (new DataClassDumper())->dump($definition);
        $constructor = (new FunctionalConstructorDumper())->dump($definition);
        $accessors = (new FunctionalAccessorsDumper())->dump($definition);
        $setters = (new FunctionalSettersDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($constructor . $accessors . $setters))->dump($definition);

        return $class . $functions;
    }
}
