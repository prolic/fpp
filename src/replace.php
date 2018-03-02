<?php

declare(strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace(
    Definition $definition,
    ?Constructor $constructor,
    string $template,
    DefinitionCollection $collection,
    array $builders = null
): string {
    if (null === $builders) {
        $builders = defaultBuilders();
    }

    foreach ($builders as $placeHolder => $builder) {
        $template = str_replace('{{' . $placeHolder . '}}', $builder($definition, $constructor, $collection, '{{' . $placeHolder . '}}'), $template);
    }

    if ($constructor) {
        $constructorString = buildConstructor($constructor, $definition);

        if ('' !== $constructorString) {
            $template = str_replace('{{constructor}}', $constructorString, $template);
        }
    }

    foreach ($builders as $placeHolder => $builder) {
        $template = str_replace('{{' . $placeHolder . '}}', '', $template);
    }

    // clean up
    $template = str_replace("        \n", "\n", $template);
    $template = str_replace("    \n", "\n", $template);
    $template = str_replace("\n\n    }\n}", "\n    }\n}", $template);

    return $template . "\n";
}
