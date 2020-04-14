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

use Fpp\Type\Enum\Constructor;
use Fpp\Type\EnumType;
use Fpp\Type\NamespaceType;

function assignment(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($a)->_(char('=')),
        __($_)->_(spaces())
    )->yields($a);
}

function typeName(): Parser
{
    return for_(
        __($x)->_(plus(letter(), char('_'))),
        __($xs)->_(many(plus(alphanum(), char('_')))),
        __($c)->_(new Parser(function ($s) use (&$x, &$xs) {
            $c = $x . $xs;

            return isKeyword($c) ? ImmList(Pair('', $c . $s)) : ImmList(Pair($c, $s));
        })),
    )->yields($c);
}

function constructorSeparator(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($s)->_(char('|')),
        __($_)->_(spaces())
    )->yields($s);
}

function imports(): Parser
{
    return plus(
        for_(
            __($_)->_(spaces()),
            __($_)->_(string('use')),
            __($_)->_(spaces1()),
            __($i)->_(sepBy1With(typeName(), char('\\'))),
            __($_)->_(nl())
        )->call(fn ($i) => Pair($i, null), $i),
        for_(
            __($_)->_(spaces()),
            __($_)->_(string('use')),
            __($_)->_(spaces1()),
            __($i)->_(sepBy1With(typeName(), char('\\'))),
            __($_)->_(spaces1()),
            __($_)->_(string('as')),
            __($_)->_(spaces1()),
            __($a)->_(typeName()),
            __($_)->_(nl())
        )->yields($i, $a)
    );
}

function singleNamespace(Parser $parserComposite): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('namespace')),
        __($_)->_(spaces1()),
        __($n)->_(sepBy1With(typeName(), char('\\'))),
        __($_)->_(nl()),
        __($is)->_(manyList(imports())),
        __($cs)->_(manyList($parserComposite))
    )->call(fn ($n, $is, $cs) => new NamespaceType($n, $is, $cs), $n, $is, $cs);
}

function multipleNamespaces(Parser $parserComposite): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('namespace')),
        __($_)->_(spaces1()),
        __($n)->_(sepBy1With(typeName(), char('\\'))),
        __($_)->_(
            for_(
                __($_)->_(spaces()),
                __($o)->_(char('{')),
                __($_)->_(spaces())
            )->yields($o),
        ),
        __($is)->_(manyList(imports())),
        __($cs)->_(manyList($parserComposite)),
        __($_)->_(
            for_(
                __($_)->_(spaces()),
                __($c)->_(char('}')),
                __($_)->_(spaces())
            )->yields($c)
        )
    )->call(fn ($n, $is, $cs) => new NamespaceType($n, $is, $cs), $n, $is, $cs);
    /*
        return for_(
            __($_)->_(spaces()),
            __($_)->_(string('namespace')),
            __($_)->_(spaces1()),
            __($n)->_(sepBy1With(typeName(), char('\\'))),
            __($cis)->_(surrounded(
                for_(
                    __($_)->_(spaces()),
                    __($o)->_(char('{')),
                    __($_)->_(spaces())
                )->yields($o),
                for_(
                    __($is)->_(manyList(imports())),
                    __($cs)->_(manyList($parserComposite))
                )->call(fn ($is, $cs) => Pair($is, $cs), $is, $cs),
                for_(
                    __($_)->_(spaces()),
                    __($c)->_(char('}')),
                    __($_)->_(spaces())
                )->yields($c)
            ))
        )->call(fn ($n, $cis) => new NamespaceType($n, $cis->_1, $cis->_2), $n, $cis);
    */
}

function enumConstructors(): Parser
{
    return for_(
        __($constructors)->_(sepBy1list(typeName(), constructorSeparator())),
        __($_)->_(nl())
    )->call(
        fn ($c) => $c->map(
            fn ($c) => new Constructor($c)
        ),
        $constructors
    );
}

const enum = 'Fpp\enum';

function enum(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('enum')),
        __($b)->_(spaces1()),
        __($t)->_(typeName()->map(fn ($c) => isKeyword($c) ? Nil() : $c)),
        __($_)->_(assignment()),
        __($cs)->_(enumConstructors())
    )->call(fn ($t, $cs) => new EnumType($t, $cs), $t, $cs);
}
