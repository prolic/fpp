<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class UuidDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $class = (new UuidClassDumper())->dump($definition);
        $functionalConstructor = (new FunctionalUuidConstructorDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($functionalConstructor))->dump($definition);

        return $class . $functions;
    }
}
