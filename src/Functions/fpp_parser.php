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

use Phunkie\Types\ImmList;
use Phunkie\Types\ImmMap;

const assignment = 'Fpp\assignment';

function assignment(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($a)->_(char('=')),
        __($_)->_(spaces())
    )->yields($a);
}

const typeName = 'Fpp\typeName';

function typeName(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($x)->_(plus(letter(), char('_'))),
        __($xs)->_(many(plus(alphanum(), char('_')))),
        __($t)->_(new Parser(function (string $s) use (&$x, &$xs) {
            return isKeyword($x . $xs) ? Nil() : ImmList(Pair($x . $xs, $s));
        })),
    )->yields($t);
}

const constructorSeparator = 'Fpp\constructorSeparator';

function constructorSeparator(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($s)->_(char('|')),
        __($_)->_(spaces())
    )->yields($s);
}

const imports = 'Fpp\imports';

function imports(): Parser
{
    return plus(
        for_(
            __($_)->_(spaces()),
            __($_)->_(string('use')),
            __($_)->_(spaces1()),
            __($i)->_(sepBy1With(typeName(), char('\\'))),
            __($_)->_(spaces()),
            __($_)->_(char(';'))
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
            __($_)->_(spaces()),
            __($_)->_(char(';'))
        )->yields($i, $a)
    );
}

const singleNamespace = 'Fpp\singleNamespace';

function singleNamespace(Parser $parserComposite): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('namespace')),
        __($_)->_(spaces1()),
        __($n)->_(sepBy1With(typeName(), char('\\'))),
        __($_)->_(spaces()),
        __($_)->_(char(';')),
        __($_)->_(spaces()),
        __($is)->_(manyList(imports())),
        __($_)->_(spaces()),
        __($ts)->_(manyList($parserComposite)),
    //)->call(fn ($n, $is, $cs) => new Namespace_($n, $is, $cs), $n, $is, $cs);
    )->call(function (string $n, ImmList $ts, ImmList $is): ImmMap {
        $ds = [];

        $ts->map(function (Type $t) use (&$ds, $n, $is) {
            $ds[$n . '\\' . $t->classname()] = new Definition($n, $t, $is);
        });

        return \ImmMap($ds);
    }, $n, $ts, $is);
}

const multipleNamespaces = 'Fpp\multipleNamespaces';

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
        __($ts)->_(manyList($parserComposite)),
        __($_)->_(
            for_(
                __($_)->_(spaces()),
                __($c)->_(char('}')),
                __($_)->_(spaces())
            )->yields($c)
        )
    //)->call(fn ($n, $is, $cs) => new Namespace_($n, $is, $cs), $n, $is, $cs);
    )->call(function (string $n, ImmList $ts, ImmList $is): ImmMap {
        $ds = [];

        $ts->map(function (Type $t) use (&$ds, $n, $is) {
            $ds[$n . '\\' . $t->classname()] = new Definition($n, $t, $is);
        });

        return \ImmMap($ds);
    }, $n, $ts, $is);
}
