<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class FunctionNamespaceDumper implements Dumper
{
    /**
     * @var string
     */
    private $innerCode;

    public function __construct(string $innerCode)
    {
        $this->innerCode = $innerCode;
    }

    public function dump(Definition $definition): string
    {
        $code = "namespace {$definition->namespace()}";

        if ($definition->namespace() !== '') {
            $code .= '\\';
        }

        return substr("$code{$definition->name()} {\n$this->innerCode", 0, -1) . "}\n\n";
    }
}
