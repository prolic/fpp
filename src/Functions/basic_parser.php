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

use const Phunkie\Functions\numbers\negate;

function result($a): Parser
{
    return new Parser(fn (string $s) => [Pair($a, $s)]);
}

function zero(): Parser
{
    return new Parser(fn (string $s) => []);
}

function el(): Parser
{
    return many1(nl())->map(fn ($xs) => $xs);
}

function item(): Parser
{
    //return new Parser(function (string $s) { var_dump($s); return \strlen($s) === 0 ? [] : array(Pair($s[0], \substr($s, 1))); });
    return new Parser(fn (string $s) => \strlen($s) === 0 ? [] : [Pair($s[0], \substr($s, 1))]);
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
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return sat(fn ($input) => $input === $c);
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) use ($c) {
        if (\strlen($s) === 0 || $s[0] !== $c) {
            return [];
        }

        return [Pair($c, \substr($s, 1))];
    });
}

function digit(): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return sat('is_numeric');
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) {
        if (\strlen($s) === 0 || ! \is_numeric($s[0])) {
            return [];
        }

        return [Pair($s[0], \substr($s, 1))];
    });
}

function lower(): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return sat('ctype_lower');
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) {
        if (\strlen($s) === 0 || ! \ctype_lower($s[0])) {
            return [];
        }

        return [Pair($s[0], \substr($s, 1))];
    });
}

function upper(): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return sat('ctype_upper');
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) {
        if (\strlen($s) === 0 || ! \ctype_upper($s[0])) {
            return [];
        }

        return [Pair($s[0], \substr($s, 1))];
    });
}

function plus(Parser $p, Parser $q): Parser
{
    return $p->or($q);
}

function letter(): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return plus(lower(), upper());
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) {
        if (\strlen($s) === 0 || ! \ctype_alpha($s[0])) {
            return [];
        }

        return [Pair($s[0], \substr($s, 1))];
    });
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
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return plus(letter(), digit());
     *
     * Now to our hacky implementation, which is way faster.
    */
    return new Parser(function (string $s) {
        if (\strlen($s) === 0 || ! \ctype_alnum($s[0])) {
            return [];
        }

        return [Pair($s[0], \substr($s, 1))];
    });
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
            return [Pair('', $s)];
        }

        $m = [];
        if (\preg_match('/^(\s+)/', $s, $m)) {
            return [Pair($m[0], \substr($s, \strlen($m[0])))];
        }

        return [Pair('', $s)];
    });
}

function spaces1(): Parser
{
    return many1(plus(char(' '), char("\n")));
}

function comma(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($c)->_(char(',')),
        __($_)->_(spaces()),
    )->yields($c);
}

function word(): Parser
{
    return plus(letter()->flatMap(
        fn ($x) => word()->map(
            fn ($xs) => $x . $xs
        )
    ), result(''));
}

function string($str): Parser
{
    /*
     * Because PHP's compiler is too stupid to work in a FP manner,
     * we need to speed things up and hack a little into this function.
     * We parse spaces a lot, so this really speeds things up.
     *
     * This is the real implementation:
     *
     * return \strlen($s)
     *   ? for_(
     *       __($c)->_(char($s[0])),
     *       __($cs)->_(string(\substr($s, 1)))
     *       )->call(concat, $c, $cs)
     *   : result('');
     *
     * Now to our hacky implementation, which is way faster.
     */
    return new Parser(function ($s) use ($str) {
        $length = \strlen($str);
        $value = \substr($s, 0, $length);

        if ($value === $str) {
            return [Pair($value, \substr($s, $length))];
        }

        return [];
    });
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
    )->call(fn ($x, $xs) => $x . $xs, $x, $xs);
}

function manyList(Parser $p): Parser
{
    return plus($p->flatMap(
        fn ($x) => manyList($p)->map(
            fn ($xs) => \is_array($xs) ? \array_merge([$x], $xs) : [$x, $xs]
        )
    ), result([]));
}

function manyList1(Parser $p): Parser
{
    return $p->flatMap(
        fn ($x) => manyList($p)->map(
            fn ($xs) => \is_array($xs) ? \array_merge([$x], $xs) : [$x, $xs]
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

function sepByList(Parser $p, Parser $sep): Parser
{
    return $p->flatMap(
        fn ($x) => manyList($sep->flatMap(
            fn ($_) => $p->map(fn ($y) => $y)
        ))->map(
            fn ($xs) => \is_array($xs) ? \array_merge([$x], $xs) : [$x, $xs]
        )
    );
}

function sepBy1list(Parser $p, Parser $sep): Parser
{
    return $p->flatMap(
        fn ($x) => manyList1($sep->flatMap(
            fn ($_) => $p->map(fn ($y) => $y)
        ))->map(
            fn ($xs) => \is_array($xs) ? \array_merge([$x], $xs) : [$x, $xs]
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
