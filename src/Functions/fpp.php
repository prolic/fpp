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
use Phunkie\Types\ImmList;
use Phunkie\Types\ImmMap;
use Phunkie\Types\Pair;

function isKeyword(string $string): bool
{
    return \in_array(\strtolower($string), [
        '__halt_compiler', 'abstract', 'and', 'array', 'as',
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

function locatePsrPath(array $prefixesPsr4, array $prefixesPsr0, string $classname): string
{
    // PSR-4 lookup
    $logicalPathPsr4 = \strtr($classname, '\\', DIRECTORY_SEPARATOR);

    foreach ($prefixesPsr4 as $prefix => $dirs) {
        if (0 === \strpos($classname, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . \substr($logicalPathPsr4, \strlen($prefix)) . '.php';
        }
    }

    // PSR-0 lookup
    $pos = \strrpos($classname, '\\');
    $logicalPathPsr0 = \substr($logicalPathPsr4, 0, $pos + 1)
        . \strtr(\substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);

    foreach ($prefixesPsr0 as $prefix => $dirs) {
        if (0 === \strpos($classname, $prefix)) {
            $dir = $dirs[0];

            return $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0 . '.php';
        }
    }

    throw new \RuntimeException(
        'Could not find psr-autoloading path for ' . $classname . ', check your composer.json'
    );
}

const parseFile = 'Fpp\parseFile';

function parseFile(Parser $parser): Parser
{
    return singleNamespace($parser)->map(fn ($n) => ImmList($n))
        ->or(manyList(multipleNamespaces($parser)));
}

/**
 * @param ImmMap<string, Definition> $definitions
 *   An immutable map of parsed fqcn and its definitions
 *
 * @return ImmMap<string, string>
 *   An immutable map of printed file content, and its fqcn
 */
function dump(Definition $definition, ImmMap $definitions, Configuration $config): ImmMap
{
    $builder = $config->builderFor($definition->type());

    return addComment(
        $builder($definition, $definitions, $config),
        $config->comment()
    )->map(
        fn (Pair $p) => Pair(($config->printer())()->printFile($p->_1), $p->_2)
    );
}

function buildDefaultPhpFile(Definition $definition, Configuration $config): PhpFile
{
    $file = new PhpFile();
    $file->setStrictTypes($config->useStrictTypes());

    $namespace = $file->addNamespace($definition->namespace());

    $definition->imports()->map(fn (Pair $i) => $namespace->addUse($i->_1, $i->_2));

    return $file;
}

/**
 * @param ImmMap<string, PhpFile> $files
 * @return ImmList<PhpFile>
 */
function addComment(ImmMap $files, ?string $comment): ImmMap
{
    if (null === $comment) {
        return $files;
    }

    return $files->map(function (Pair $p) use ($comment) {
        /** @var PhpFile $file */
        $file = $p->_1;

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $class->addComment($comment);
            }
        }

        return Pair($file, $p->_2);
    });
}
