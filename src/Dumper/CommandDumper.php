<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

final class CommandDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $class = (new CommandClassDumper())->dump($definition);
        $functionalConstructor = (new FunctionalConstructorDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($functionalConstructor))->dump($definition);

        return $class . $functions;
    }
}
