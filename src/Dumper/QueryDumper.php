<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class QueryDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $class = (new QueryClassDumper())->dump($definition);
        $functionalConstructor = (new FunctionalConstructorDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($functionalConstructor))->dump($definition);

        return $class . $functions;
    }
}
