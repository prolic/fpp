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

use Phunkie\Cats\Monad;
use function Phunkie\Functions\show\showType;
use Phunkie\Types\Kind;
use Phunkie\Types\Pair;

/**
 * Class Parser<A>
 * wraps:
 *     string -> List<Pair(A , string)>
 */
class Parser implements Monad, Kind
{
    /**
     * @var callable string -> List<Pair(A, string)>
     */
    private $run;

    public function __construct(callable $run)
    {
        $this->run = $run;
    }

    /**
     * string -> array<Pair(A, string)>
     */
    public function run(string $input): array
    {
        return ($this->run)($input);
    }

    /**
     * (A => Parser<B>) => Parser(B)
     */
    public function flatMap(callable $f): Kind
    {
        return new Parser(function (string $s) use ($f) {
            return flatMap(
                fn (Pair $result) => $f($result->_1)->run($result->_2),
                $this->run($s)
            );
        });

        return new Parser(fn (string $s) => $this->run($s)->flatMap(
            fn (Pair $result) => $f($result->_1)->run($result->_2)
        ));
    }

    /**
     * (A => B) => Parser<B>
     */
    public function map(callable $f): Kind
    {
        return new Parser(fn (string $s) => \array_map(
            fn (Pair $result) => \Pair($f($result->_1), $result->_2),
            $this->run($s)
        ));
    }

    /**
     * (A => B) => (Parser<A> => Parser<B>)
     */
    public function lift($f): callable
    {
        return fn (Parser $a) => new Parser(fn ($x) => $a->run($f($x)));
    }

    /**
     * B => Parser<B>
     */
    public function as($b): Kind
    {
        return result($b);
    }

    /**
     * () => Parser<Unit>
     */
    public function void(): Kind
    {
        return result(Unit());
    }

    /**
     * (A => B) => Parser<Pair<A,B>>
     */
    public function zipWith($f): Kind
    {
        return new Parser(fn ($x) => [Pair(Pair($x, $f($x))), '']);
    }

    public function imap(callable $f, callable $g): Kind
    {
        return $this->map($f);
    }

    /**
     * Parser<Parser<A>> => Parser<A>
     */
    public function flatten(): Kind
    {
        return $this->run(_)->head->_1;
    }

    public function or(Parser $another): Parser
    {
        return new Parser(fn (string $s) => \array_merge($this->run($s), $another->run($s)));
    }

    // @todo do we need this?
    public function sepBy1(Parser $sep)
    {
        return for_(
            __($x)->_($this),
            __($xs)->_(many(for_(
                __($_)->_($sep),
                __($y)->_($this)
            )->yields($y)))
        )->call(fn ($x, $xs) => $x . $xs, $x, $xs);
    }

    public function sepBy1With(Parser $sep)
    {
        return for_(
            __($x)->_($this),
            __($xs)->_(many(for_(
                __($s)->_($sep),
                __($y)->_($this)
            )->call(fn ($x, $y) => $x . $y, $s, $y)))
        )->call(fn ($x, $xs) => $x . $xs, $x, $xs);
    }

    public function getTypeArity(): int
    {
        return 1;
    }

    public function getTypeVariables(): array
    {
        return [showType($this->run)];
    }
}
