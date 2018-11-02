<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

const replace = '\Fpp\replace';

function replace(
    string $template,
    Definition $definition,
    ?Constructor $constructor,
    DefinitionCollection $collection,
    array $builders = null
): string {
    if (null === $builders) {
        $builders = defaultBuilders();
    }

    foreach ($builders as $placeHolder => $builder) {
        $searchString = '{{' . $placeHolder . '}}';

        if (false !== \strpos($template, $searchString)) {
            $template = \str_replace($searchString, $builder($definition, $constructor, $collection, '{{' . $placeHolder . '}}'), $template);
        }
    }

    foreach ($builders as $placeHolder => $builder) {
        $needles = [
            "\n        {{{$placeHolder}}}\n",
            "\n    {{{$placeHolder}}}\n\n",
            "\n    {{{$placeHolder}}}\n",
            "\n{{{$placeHolder}}}\n",
        ];
        $template = \str_replace($needles, "\n", $template);
    }

    // clean up

    $needles = [
        "\n    }\n    ",
        "\n\n\n",
        "\n    \n",
        "\n\n}\n}",
        "\n\n    }\n",
        "    }\n\n}\n",
    ];

    $replaces = [
        "\n    }\n\n    ",
        "\n\n",
        "\n",
        "\n}\n}",
        "\n    }\n",
        "    }\n}\n",
    ];

    $template = \str_replace($needles, $replaces, $template);

    return $template . "\n";
}
