<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Nette\PhpGenerator\PhpFile;
use Phunkie\Types\Pair;

const flatMap = 'Fpp\flatMap';

function flatMap(callable $f, array $arr): array
{
    $b = [];

    foreach ($arr as $a) {
        $tmp = $f($a);

        if (null === $tmp) {
            break;
        }

        if (\is_array($tmp)) {
            foreach ($tmp as $v) {
                $b[] = $v;
            }
            break;
        }

        $b[] = $tmp;
    }

    return $b;
}

const isKeyword = 'Fpp\isKeyword';

function isKeyword(string $string): bool
{
    return \in_array(\strtolower($string), [
        '__halt_compiler', 'abstract', 'and', 'as',
        'break', 'callable', 'case', 'catch', 'class',
        'clone', 'const', 'continue', 'declare', 'default',
        'die', 'do', 'echo', 'else', 'elseif',
        'empty', 'enddeclare', 'endfor', 'endforeach', 'endif',
        'endswitch', 'endwhile', 'eval', 'exit', 'extends',
        'final', 'finally', 'for', 'foreach', 'function',
        'global', 'goto', 'if', 'implements', 'include',
        'include_once', 'instanceof', 'insteadof', 'interface', 'isset',
        'list', 'namespace', 'new', 'or', 'print',
        'private', 'protected', 'public', 'require', 'require_once',
        'return', 'static', 'switch', 'throw', 'trait',
        'try', 'unset', 'use', 'var', 'while',
        'xor', 'yield', 'yield from',
    ], true);
}

const locatePsrPath = 'Fpp\locatePsrPath';

function locatePsrPath(array $prefixesPsr4, array $prefixesPsr0, string $classname): string
{
    // PSR-4 lookup
    $logicalPath = \strtr($classname, '\\', DIRECTORY_SEPARATOR);

    foreach ($prefixesPsr4 as $prefix => $dirs) {
        if (0 === \strpos($classname, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . \substr($logicalPath, \strlen($prefix)) . '.php';
        }
    }

    // PSR-0 lookup
    $pos = \strrpos($classname, '\\');

    foreach ($prefixesPsr0 as $prefix => $dirs) {
        if (0 === \strpos($classname, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . \substr($classname, $pos + 1) . '.php';
        }
    }

    throw new \RuntimeException(
        'Could not find psr-autoloading path for ' . $classname . ', check your composer.json'
    );
}

const parseFile = 'Fpp\parseFile';

function parseFile(Parser $parser): Parser
{
    return singleNamespace($parser)->map(fn ($n) => [$n])
        ->or(manyList(multipleNamespaces($parser)));
}

const dump = 'Fpp\dump';

/**
 * @param array<string, Definition> $definitions
 *   An immutable map of parsed fqcn and its definitions
 *
 * @return array<string, string>
 *   An immutable map of printed file content, and its fqcn
 */
function dump(Definition $definition, array $definitions, Configuration $config): array
{
    $builder = $config->builderFor($definition->type());

    $files = addComment(
        $builder($definition, $definitions, $config),
        $config->comment()
    );

    foreach ($files as $fqcn => $file) {
        $files[$fqcn] = ($config->printer())()->printFile($file);
    }

    return $files;
}

const buildDefaultPhpFile = 'Fpp\buildDefaultPhpFile';

function buildDefaultPhpFile(Definition $definition, Configuration $config): PhpFile
{
    $file = new PhpFile();
    $file->setStrictTypes($config->useStrictTypes());

    $namespace = $file->addNamespace($definition->namespace());

    \array_map(
        fn (Pair $i) => $namespace->addUse($i->_1, $i->_2),
        $definition->imports()
    );

    return $file;
}

const addComment = 'Fpp\addComment';

/**
 * @param array<string, PhpFile> $files
 * @return array<PhpFile>
 */
function addComment(array $files, ?string $comment): array
{
    foreach ($files as $fqcn => $file) {
        /** @var PhpFile $file */
        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                if (null !== $comment) {
                    $class->addComment($comment);
                }
                $class->addComment('@codeCoverageIgnore');
            }
        }
    }

    return $files;
}

const parseArguments = 'Fpp\parseArguments';

function parseArguments(): Parser
{
    return surrounded(
        for_(
            __($o)->_(char('{')),
        )->yields($o),
        sepByList(
            for_(
                __($_)->_(spaces()),
                __($n)->_(char('?')->or(result(''))),
                __($at)->_(typeName()->or(result(''))),
                __($l)->_(string('[]')->or(result(''))),
                __($_)->_(spaces()),
                __($x)->_(
                    for_(
                        __($_)->_(char('$')),
                        __($x)->_(plus(letter(), char('_'))),
                        __($xs)->_(many(plus(alphanum(), char('_')))),
                        __($_)->_(spaces()),
                    )->call(fn ($x, $xs) => $x . $xs, $x, $xs)
                    ->or(result(''))
                ),
                __($e)->_(char('=')->or(result(''))),
                __($_)->_(spaces()),
                __($d)->_(
                    int()
                        ->or(string('null'))
                        ->or(string('[]'))
                        ->or(string('\'\''))
                        ->or(surroundedWith(char('\''), many(not('\'')), char('\'')))->or(result(''))
                ),
            )->call(
                fn ($at, $x, $n, $l, $e, $d) => new Argument(
                    $x,
                    '' === $at ? null : $at,
                    $n === '?',
                    '[]' === $l,
                    '=' === $e ? $d : null
                ),
                $at,
                $x,
                $n,
                $l,
                $e,
                $d
            ),
            char(',')
        ),
        for_(
            __($_)->_(spaces()),
            __($c)->_(char('}')),
        )->yields($c)
    );
}

const resolveType = 'Fpp\resolveType';

/**
 * Resolves from class name to fully qualified class name,
 * f.e. Bar => Foo\Bar
 */
function resolveType(?string $type, Definition $definition): ?string
{
    if (\in_array($type, [null, 'string', 'int', 'bool', 'float', 'array'], true)) {
        return $type;
    }

    foreach ($definition->imports() as $p) {
        $import = $p->_1;
        $alias = $p->_2;

        if ($alias === $type) {
            return $import;
        }

        if (null === $alias && $type === $import) {
            return $type;
        }

        $pos = \strrpos($import, '\\');

        if (false !== $pos && $type === \substr($import, $pos + 1)) {
            return $import;
        }
    }

    return $definition->namespace() . '\\' . $type;
}

const calculateDefaultValue = 'Fpp\calculateDefaultValue';

/** @return mixed */
function calculateDefaultValue(Argument $a)
{
    if ($a->isList() && $a->defaultValue() === '[]') {
        return [];
    }

    switch ($a->type()) {
        case 'int':
            return null === $a->defaultValue() ? null : (int) $a->defaultValue();
            break;
        case 'float':
            return null === $a->defaultValue() ? null : (float) $a->defaultValue();
        case 'bool':
            return null === $a->defaultValue() ? null : 'true' === $a->defaultValue();
        case 'string':
            if (null === $a->defaultValue()) {
                return null;
            }

            return $a->defaultValue() === "''" ? '' : \substr($a->defaultValue(), 1, -1);
        case 'array':
            return $a->defaultValue() === '[]' ? [] : $a->defaultValue();
        case null:
        default:
            // yes both cases
            return $a->defaultValue();
    }
}

const renameDuplicateArgumentNames = 'Fpp\renameDuplicateArgumentNames';

/**
 * @param array<string, int> $names
 * @param list<Argument> $arguments
 *
 * @return list<Argument>
 */
function renameDuplicateArgumentNames(array $names, array $arguments): array
{
    $result = [];

    foreach ($arguments as $argument) {
        /** @var Argument $argument */
        $name = $argument->name();

        if (! isset($names[$name])) {
            $names[$name] = 1;
            $result[] = $argument;
        } else {
            $names[$name]++;
            $result[] = new Argument(
                $name . (string) $names[$name],
                $argument->type(),
                $argument->nullable(),
                $argument->isList(),
                $argument->defaultValue()
            );
        }
    }

    return $result;
}

const generateValidationFor = 'Fpp\generateValidationFor';

function generateValidationFor(
    Argument $a,
    string $paramName,
    ?string $resolvedType,
    array $definitions,
    Configuration $config
): string {
    if ($a->isList()) {
        $code = "if (! isset({$paramName}['{$a->name()}']) || ! \is_array({$paramName}['{$a->name()}'])) {\n";
        $code .= "    throw new \InvalidArgumentException('Error on \"{$a->name()}\": array expected');\n";
        $code .= "}\n\n";

        return $code;
    }

    if ($a->nullable()) {
        $code = "if (isset({$paramName}['{$a->name()}']) && ! {%validation%}) {\n";
        $code .= "    throw new \InvalidArgumentException('{%validationErrorMessage%}');\n";
        $code .= "}\n\n";
    } else {
        $code = "if (! isset({$paramName}['{$a->name()}']) || ! {%validation%}) {\n";
        $code .= "    throw new \InvalidArgumentException('{%validationErrorMessage%}');\n";
        $code .= "}\n\n";
    }

    switch ($a->type()) {
        case null:
            return '';
        case 'int':
            $validation = "\is_int({$paramName}['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\": int expected";
            break;
        case 'float':
            $validation = "\is_float({$paramName}['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\": float expected";
            break;
        case 'bool':
            $validation = "\is_bool({$paramName}['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\": bool expected";
            break;
        case 'string':
            $validation = "\is_string({$paramName}['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\": string expected";
            break;
        case 'array':
            $validation = "\is_array({$paramName}['{$a->name()}'])";
            $validationErrorMessage = "Error on \"{$a->name()}\": array expected";
            break;
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfig */
                $typeConfig = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfig) {
                    return '';
                }

                $validator = $typeConfig->validator();
                $validatorErrorMessage = $typeConfig->validationErrorMessage();
            } else {
                $type = $definition->type();

                $validator = $config->validatorFor($type);
                $validatorErrorMessage = $config->validationErrorMessageFor($type);
            }

            $validation = $validator($a->type(), "{$paramName}['{$a->name()}']");
            $validationErrorMessage = $validatorErrorMessage("{$a->name()}");

            break;
    }

    return \str_replace(
        [
            '{%validation%}',
            '{%validationErrorMessage%}',
        ],
        [
            $validation,
            $validationErrorMessage,
        ],
        $code
    );
}

const generateFromPhpValueFor = 'Fpp\generateFromPhpValueFor';

function generateFromPhpValueFor(
    Argument $a,
    string $paramName,
    int $intentLevel,
    ?string $resolvedType,
    array $definitions,
    Configuration $config
): string {
    $intent = \str_repeat(' ', $intentLevel * 4);

    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            if ($a->nullable()) {
                return "{$intent}{$paramName}['{$a->name()}'] ?? null";
            }

            return "{$intent}{$paramName}['{$a->name()}']";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfig */
                $typeConfig = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfig) {
                    return "{$intent}{$paramName}['{$a->name()}']";
                }

                if ($a->isList()) {
                    return <<<CODE
{$intent}\array_map(fn (\$e) => {$typeConfig->fromPhpValue()($a->type(), '$e')}, {$paramName}['{$a->name()}'])
CODE;
                }

                return $intent . $typeConfig->fromPhpValue()($a->type(), "{$paramName}['{$a->name()}']");
            }

            $builder = $config->fromPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn(\$e) => {$builder($definition->type(), '$e')}";

                return "{$intent}\array_map($callback, {$paramName}['{$a->name()}'])";
            }

            if ($a->nullable()) {
                return "{$intent}isset({$paramName}['{$a->name()}']) ? " . $builder($definition->type(), "{$paramName}['{$a->name()}']") . ' : null';
            }

            return $intent . $builder($definition->type(), "{$paramName}['{$a->name()}']") . '';
    }
}

function generateToArrayBodyFor(Argument $a, $prefix, ?string $resolvedType, array $definitions, Configuration $config): string
{
    switch ($a->type()) {
        case null:
        case 'int':
        case 'float':
        case 'bool':
        case 'string':
        case 'array':
            // yes all above are treated the same
            return "    '{$a->name()}' => {$prefix}{$a->name()},\n";
        default:
            $definition = $definitions[$resolvedType] ?? null;

            if (null === $definition) {
                /** @var TypeConfiguration|null $typeConfiguration */
                $typeConfiguration = $config->types()[$resolvedType] ?? null;

                if (null === $typeConfiguration) {
                    return "    '{$a->name()}' => {$prefix}{$a->name()},\n";
                }

                if ($a->isList()) {
                    $callback = "fn({$a->type()} \$e) => {$typeConfiguration->toPhpValue()($a->type(), '$e')}";

                    return <<<CODE
    '{$a->name()}' => \array_map($callback, {$prefix}{$a->name()}),

CODE;
                }

                if ($a->nullable()) {
                    return "    '{$a->name()}' => {$prefix}{$a->name()} !== null ? "
                        . ($typeConfiguration->toPhpValue()($a->type(), '$this->' . $a->name())) . " : null,\n";
                }

                return "    '{$a->name()}' => " . ($typeConfiguration->toPhpValue()($a->type(), $prefix . $a->name())) . ",\n";
            }

            $builder = $config->toPhpValueFor($definition->type());

            if ($a->isList()) {
                $callback = "fn({$a->type()} \$e) => {$builder($definition->type(), '$e')}";

                return "    '{$a->name()}' => \array_map($callback, {$prefix}{$a->name()}),\n";
            }

            if ($a->nullable()) {
                return "    '{$a->name()}' => {$prefix}{$a->name()} !== null ? {$prefix}"
                    . ($builder)($definition->type(), $a->name()) . " : null,\n";
            }

            return "    '{$a->name()}' => {$prefix}" . ($builder)($definition->type(), $a->name()) . ",\n";
    }
}
