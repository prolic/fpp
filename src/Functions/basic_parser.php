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

use function ImmList;
use function Nil;
use const Phunkie\Functions\immlist\concat;
use const Phunkie\Functions\numbers\negate;
use Phunkie\Types;

function result($a): Parser
{
    return new Parser(fn (string $s) => ImmList(Pair($a, $s)));
}

function zero(): Parser
{
    return new Parser(fn (string $s) => Nil());
}

function el(): Parser
{
    return many1(nl())->map(fn ($xs) => $xs);
}

function item(): Parser
{
    //return new Parser(function (string $s) { var_dump($s); return \strlen($s) === 0 ? Nil() : ImmList(Pair($s[0], \substr($s, 1))); });
    return new Parser(fn (string $s) => \strlen($s) === 0 ? Nil() : ImmList(Pair($s[0], \substr($s, 1))));
}

function seq(Parser $p, Parser $q): Parser
{
    return for_(
        __($x)->_($p),
        __($y)->_($q)
    )->yields($x, $y);
}

function sat(callable $predicate): Parser
{
    return item()->flatMap(
        fn ($x) => $predicate($x) ? result($x) : zero()
    );
}

function not($c): Parser
{
    return sat(fn ($s) => $s !== $c);
}

function manyNot($c): Parser
{
    return many1(not($c));
}

function char($c): Parser
{
    return sat(fn ($input) => $input === $c);
}

function digit(): Parser
{
    return sat('is_numeric');
}

function lower(): Parser
{
    return sat('ctype_lower');
}

function upper(): Parser
{
    return sat('ctype_upper');
}

function plus(Parser $p, Parser $q): Parser
{
    return $p->or($q);
}

function letter(): Parser
{
    return plus(lower(), upper());
}

function nl(): Parser
{
    return for_(
        __($_)->_(many(char(' '))),
        __($nl)->_(sat(fn (string $s) => $s === \PHP_EOL))
    )->yields($nl);
}

function alphanum(): Parser
{
    return plus(letter(), digit());
}

function spaces(): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return many(plus(char(' '), char("\n")));
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function ($s) {
        if (\strlen($s) === 0) {
            return ImmList(Pair('', $s));
        }

        $m = [];
        if (\preg_match('/^(\s+)/', $s, $m)) {
            return ImmList(Pair($m[0], \substr($s, \strlen($m[0]))));
        }

        return ImmList(Pair('', $s));
    });
}

function spaces1(): Parser
{
    return many1(plus(char(' '), char("\n")));
}

function word(): Parser
{
    return plus(letter()->flatMap(
        fn ($x) => word()->map(
            fn ($xs) => $x . $xs
        )
    ), result(''));
}

function string($s): Parser
{
    return \strlen($s) ?

        for_(
            __($c)->_(char($s[0])),
            __($cs)->_(string(\substr($s, 1)))
        )->call(concat, $c, $cs) :

        result('');
}

function many(Parser $p): Parser
{
    return plus($p->flatMap(
        fn ($x) => many($p)->map(
            fn ($xs) => $x . $xs)
        ),
        result('')
    );
}

function many1(Parser $p): Parser
{
    return for_(
        __($x)->_($p),
        __($xs)->_(many($p))
    )->call(concat, $x, $xs);
}

function manyList(Parser $p): Parser
{
    return plus($p->flatMap(
        fn ($x) => manyList($p)->map(
            fn ($xs) => $xs instanceof Types\ImmList ? ImmList($x)->combine($xs) : ImmList($x, $xs)
        )
    ), result(Nil()));
}

function manyList1(Parser $p): Parser
{
    return $p->flatMap(
        fn ($x) => manyList($p)->map(
            fn ($xs) => $xs instanceof Types\ImmList ? ImmList($x)->combine($xs) : ImmList($x, $xs)
        )
    );
}

function nat(): Parser
{
    return many1(digit())->map(fn ($xs) => (int) $xs);
}

function int(): Parser
{
    return plus(for_(
        __($_)->_(char('-')),
        __($n)->_(nat())
    )->call(negate, $n), nat());
}

function sepBy1(Parser $p, Parser $sep): Parser
{
    return $p->sepBy1($sep);
}

function sepBy1With(Parser $p, Parser $sep): Parser
{
    return $p->sepBy1With($sep);
}

function sepBy1list(Parser $p, Parser $sep): Parser
{
    return $p->flatMap(
        fn ($x) => manyList1($sep->flatMap(
            fn ($_) => $p->map(fn ($y) => $y)
        ))->map(
            fn ($xs) => $xs instanceof Types\ImmList ? ImmList($x)->combine($xs) : ImmList($x, $xs)
        )
    );
}

function surrounded(Parser $open, Parser $p, Parser $close): Parser
{
    return for_(
        __($_)->_($open),
        __($ns)->_($p),
        __($_)->_($close)
    )->yields($ns);
}

function surroundedWith(Parser $open, Parser $p, Parser $close): Parser
{
    return for_(
        __($o)->_($open),
        __($ns)->_($p),
        __($c)->_($close)
    )->call(fn ($o, $ns, $c) => $o . $ns . $c, $o, $ns, $c);
}

function ints(): Parser
{
    return surrounded(
        char('['),
        int()->sepBy1(char(',')),
        char(']')
    );
}
