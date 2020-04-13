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

use Fpp\Type\NamespaceType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Printer;

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

function dump(Printer $printer, $type, NamespaceType $ns, array $config)
{
    if (! isset($config['types'][\get_class($type)])) {
        throw new \RuntimeException('No builder found for ' . \get_class($type));
    }

    $builder = $config['types'][\get_class($type)]->_2;

    $file = new PhpFile();
    $file->setStrictTypes($config['use_strict_types']);

    $namespace = $file->addNamespace($ns->name());

    $namespace->add($builder($type));

    return $printer->printFile($file);
}
