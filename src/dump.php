<?php

declare(strict_types=1);

namespace Fpp;

const dump = '\Fpp\dump';

function dump(DefinitionCollection $collection, callable $loadTemplate, callable $replace): string
{
    $code = <<<CODE
<?php
// this file is auto-generated by prolic/fpp
// don't edit this file manually

declare(strict_types=1);


CODE;

    foreach ($collection->definitions() as $definition) {
        $code .= $replace($definition, $loadTemplate($definition));
    }

    return $code;
}