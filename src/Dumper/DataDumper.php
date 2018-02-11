<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;
use Fpp\DefinitionCollection;

final class DataDumper implements Dumper
{
    /**
     * @var DefinitionCollection
     */
    private $definitionCollection;

    public function __construct(DefinitionCollection $collection)
    {
        $this->definitionCollection = $collection;
    }

    public function dump(Definition $definition): string
    {
        $class = (new DataClassDumper($this->definitionCollection))->dump($definition);
        $constructor = (new FunctionalConstructorDumper())->dump($definition);
        $accessors = (new FunctionalAccessorsDumper())->dump($definition);
        $setters = (new FunctionalSettersDumper())->dump($definition);
        $functions = (new FunctionNamespaceDumper($constructor . $accessors . $setters))->dump($definition);

        return $class . $functions;
    }
}
