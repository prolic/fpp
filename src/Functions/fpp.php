<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Fpp;

use Composer\Autoload\ClassLoader;
use Fpp\Type\Data\Data;
use Fpp\Type\Enum\Enum;
use Nette\PhpGenerator\PhpFile;
use Phunkie\Types\Pair;

const runFpp = 'Fpp\runFpp';

function runFpp(Configuration $config, ClassLoader $classLoader): void
{
    $locatePath = $config->locatePath($classLoader);

    $parser = \array_reduce(
        \array_filter(
            $config->types(),
            fn (TypeConfiguration $c) => $c->parse() !== null
        ),
        fn (Parser $p, TypeConfiguration $c) => $p->or($c->parse()()),
        zero()
    );

    $definitions = \array_map(
        fn ($f) => Pair(($config->fileParser())($parser)->run(\file_get_contents($f)), $f),
        scan($config->source())
    );

    $definitions = \array_map(
        function (Pair $p) {
            $parsed = $p->_1;
            $filename = $p->_2;

            $p = $parsed[0];

            if ($p->_2 !== '') {
                echo "\033[1;31mSyntax error at file: $filename\033[0m" . PHP_EOL;
                echo $p->_2;
                exit(1);
            }

            return $p->_1;
        },
        $definitions
    );

    $definitions = \array_reduce(
        $definitions,
        fn (array $l, array $nds) => \array_merge($l, $nds),
        []
    );

    $definitions = \array_reduce(
        $definitions,
        function (array $ds, array $nds) {
            foreach ($nds as $n => $d) {
                $ds[$n] = $d;
            }

            return $ds;
        },
        [],
    );

    $dumpedDefinitions = [];

    foreach ($definitions as $name => $definition) {
        $dumpedDefinitions[$name] = dump($definition, $definitions, $config);
    }

    foreach ($dumpedDefinitions as $name => $files) {
        foreach ($files as $fqcn => $code) {
            $filename = $locatePath($fqcn);
            $directory = \dirname($filename);

            if (! \is_dir($directory)) {
                \mkdir($directory, 0777, true);
            }

            \file_put_contents($filename, $code);
        }
    }
}

const registerFppTargetAutoloader = 'Fpp\registerFppTargetAutoloader';

function registerFppTargetAutoloader(string $targetDirectory): void
{
    \spl_autoload_register(
        function (string $className) use ($targetDirectory) {
            $file = $targetDirectory . DIRECTORY_SEPARATOR . \strtr($className, '\\', DIRECTORY_SEPARATOR) . '.php';

            if (\file_exists($file)) {
                require_once $file;
            }
        }
    );
}

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
                    )->call(fn ($x, $xs) => $x . $xs, $x, $xs)->or(result(''))
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
    )->or(result([]));
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

                if (null === $typeConfig && ! $a->isList()) {
                    foreach ($definitions as $definition) {
                        /** @var Definition $definition */
                        $type = $definition->type();

                        if ($type instanceof Data) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->classname()) === $resolvedType) {
                                    return $config->types()[Data::class]->fromPhpValue()(
                                        new Type\Data\Data(
                                            $constructor->classname(),
                                            $type->markers(),
                                            $type->constructors()
                                        ),
                                        $paramName . '[\'' . $a->name() . '\']'
                                    );
                                }
                            }
                        }

                        if ($type instanceof Enum) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->name()) === $resolvedType) {
                                    return $config->types()[Enum::class]->fromPhpValue()(
                                        new Type\Enum\Enum(
                                            $constructor->classname(),
                                            $type->markers(),
                                            $type->constructors()
                                        ),
                                        $paramName . '[\'' . $a->name() . '\']'
                                    );
                                }
                            }
                        }
                    }

                    return "{$intent}{$paramName}['{$a->name()}']";
                }

                if (null === $typeConfig && $a->isList()) {
                    foreach ($definitions as $definition) {
                        /** @var Definition $definition */
                        $type = $definition->type();

                        if ($type instanceof Data) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->classname()) === $resolvedType) {
                                    return "{$intent}\array_map(fn (\$e) => " . $config->types()[Data::class]->fromPhpValue()(
                                            new Type\Data\Data(
                                                $constructor->classname(),
                                                $type->markers(),
                                                $type->constructors()
                                            ),
                                            '$e'
                                        ) . ', ' . $paramName . '[\'' . $a->name() . '\'])';
                                }
                            }
                        }

                        if ($type instanceof Enum) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->name()) === $resolvedType) {
                                    return "{$intent}\array_map(fn (\$e) => " . $config->types()[Enum::class]->fromPhpValue()(
                                            new Type\Enum\Enum(
                                                $constructor->classname(),
                                                $type->markers(),
                                                $type->constructors()
                                            ),
                                            '$e'
                                        ) . ', ' . $paramName . '[\'' . $a->name() . '\'])';
                                }
                            }
                        }
                    }

                    return "{$intent}{$paramName}['{$a->name()}']";
                }

                if ($a->isList()) {
                    return <<<CODE
{$intent}\array_map(fn (\$e) => {$typeConfig->fromPhpValue()($a->type(), '$e')}, {$paramName}['{$a->name()}'])
CODE;
                }

                if ($a->nullable()) {
                    return "{$intent}isset({$paramName}['{$a->name()}']) ? "
                        . $typeConfig->fromPhpValue()($a->type(), "{$paramName}['{$a->name()}']")
                        . ' : null';
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
                    foreach ($definitions as $definition) {
                        /** @var Definition $definition */
                        $type = $definition->type();

                        if ($type instanceof Data) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->classname()) === $resolvedType) {
                                    return "    '{$a->name()}' => {$prefix}{$a->name()}->toArray(),\n";
                                }
                            }
                        }

                        if ($type instanceof Enum) {
                            foreach ($type->constructors() as $constructor) {
                                if (($definition->namespace() . '\\' . $constructor->name()) === $resolvedType) {
                                    return "    '{$a->name()}' => {$prefix}{$a->name()}->name(),\n";
                                }
                            }
                        }
                    }

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
                        . ($typeConfiguration->toPhpValue()($a->type(), $prefix . $a->name())) . " : null,\n";
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
