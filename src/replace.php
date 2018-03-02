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

    if ($constructor) {
        if (isScalarConstructor($constructor)) {
            $namespace = $definition->namespace();
        } else {
            $position = strrpos($constructor->name(), '\\');

            if (false === $position) {
                $namespace = '';
            } else {
                $namespace = substr($constructor->name(), 0, $position);
            }
        }
    } else {
        $namespace = $definition->namespace();
    }

    $template = str_replace('{{namespace_name}}', $namespace, $template);

    foreach ($builders as $placeHolder => $builder) {
        $template = str_replace('{{' . $placeHolder . '}}', $builder($definition, $constructor, $collection, '{{' . $placeHolder . '}}'), $template);
    }

    foreach ($definition->derivings() as $deriving) {
        switch ((string) $deriving) {
            case Deriving\Enum::VALUE:
                if ($constructor) {
                    $template = str_replace('{{enum_value}}', buildReferencedClass($namespace, $constructor->name()), $template);
                } else {
                    $replace = '';
                    foreach ($definition->constructors() as $definitionConstructor) {
                        $class = buildReferencedClass($namespace, $definitionConstructor->name());
                        $replace .= "            $class::VALUE => $class::class,\n";
                    }
                    $template = str_replace('{{enum_options}}', substr($replace, 12, -1), $template);
                }
                break;
        }
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
