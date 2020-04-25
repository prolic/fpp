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

use Fpp\Type\BoolType;
use Fpp\Type\Data\Argument;
use Fpp\Type\DataType;
use Fpp\Type\Enum\Constructor;
use Fpp\Type\EnumType;
use Fpp\Type\FloatType;
use Fpp\Type\IntType;
use Fpp\Type\MarkerType;
use Fpp\Type\StringType;

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

const singleNamespace = 'Fpp\singleNamespace';

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
    )->call(fn ($n, $is, $cs) => new Namespace_($n, $is, $cs), $n, $is, $cs);
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
        __($cs)->_(manyList($parserComposite)),
        __($_)->_(
            for_(
                __($_)->_(spaces()),
                __($c)->_(char('}')),
                __($_)->_(spaces())
            )->yields($c)
        )
    )->call(fn ($n, $is, $cs) => new Namespace_($n, $is, $cs), $n, $is, $cs);
}

const enum = 'Fpp\enum';

function enum(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('enum')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(assignment()),
        __($cs)->_(
            for_(
                __($constructors)->_(sepBy1list(typeName(), constructorSeparator())),
                __($_)->_(nl())
            )->call(
                fn ($c) => $c->map(
                    fn ($c) => new Constructor($c)
                ),
                $constructors
            )
        )
    )->call(fn ($t, $cs) => new EnumType($t, $cs), $t, $cs);
}

const data = 'Fpp\data';

function data(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('data')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
        __($_)->_(assignment()),
        __($as)->_(surrounded(
            for_(
                __($_)->_(spaces()),
                __($o)->_(char('{')),
                __($_)->_(spaces())
            )->yields($o),
            sepBy1list(
                for_(
                    __($_)->_(spaces()),
                    __($n)->_(char('?')->or(result(''))),
                    __($at)->_(typeName()->or(result(''))),
                    __($l)->_(string('[]')->or(result(''))),
                    __($_)->_(spaces()),
                    __($_)->_(char('$')),
                    __($x)->_(plus(letter(), char('_'))),
                    __($xs)->_(many(plus(alphanum(), char('_')))),
                    __($_)->_(spaces()),
                    __($e)->_(char('=')->or(result(''))),
                    __($_)->_(spaces()),
                    __($d)->_(
                        many(int())
                            ->or(string('null'))
                            ->or(string('[]'))
                            ->or(surroundedWith(char('\''), many(item()), char('\'')))->or(result(''))
                    ),
                )->call(
                    fn ($at, $x, $xs, $n, $l, $e, $d) => new Argument(
                        $x . $xs,
                        '' === $at ? null : $at,
                        $n === '?',
                        '[]' === $l,
                        '=' === $e ? $d : null
                    ),
                    $at,
                    $x,
                    $xs,
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
                __($_)->_(spaces())
            )->yields($c)
        ))
    )->call(fn ($t, $as) => new DataType($t, $as), $t, $as);
}

const string_ = 'Fpp\string_';

function string_(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('string')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
    )->call(fn ($t) => new StringType($t), $t);
}

const int_ = 'Fpp\int_';

function int_(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('int')),
        __($_)->_(spaces1()),
        __($t)->_(typeName())
    )->call(fn ($t) => new IntType($t), $t);
}

const float_ = 'Fpp\float_';

function float_(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('float')),
        __($_)->_(spaces1()),
        __($t)->_(typeName())
    )->call(fn ($t) => new FloatType($t), $t);
}

const bool_ = 'Fpp\bool_';

function bool_(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('bool')),
        __($_)->_(spaces1()),
        __($t)->_(typeName()),
    )->call(fn ($t) => new BoolType($t), $t);
}

const marker = 'Fpp\marker';

function marker(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('marker')),
        __($_)->_(spaces1()),
        __($m)->_(typeName()),
    )->call(fn ($m) => new MarkerType($m, Nil()), $m);
}
