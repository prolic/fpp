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

use Phunkie\Cats\Monad;
use function Phunkie\Functions\immlist\concat;
use const Phunkie\Functions\immlist\concat as concatenate;
use function Phunkie\Functions\show\showType;
use Phunkie\Types\ImmList;
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
     * string -> List<Pair(A, string)>
     */
    public function run(string $input): ImmList
    {
        return ($this->run)($input);
    }

    /**
     * (A => Parser<B>) => Parser(B)
     */
    public function flatMap(callable $f): Kind
    {
        return new Parser(fn (string $s) => $this->run($s)->flatMap(
            fn (Pair $result) => $f($result->_1)->run($result->_2)
        ));
    }

    /**
     * (A => B) => Parser<B>
     */
    public function map(callable $f): Kind
    {
        return new Parser(fn (string $s) => $this->run($s)->map(
            fn (Pair $result) => \Pair($f($result->_1), $result->_2)
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
        return new Parser(fn ($x) => ImmList(Pair(Pair($x, $f($x))), ''));
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
        return new Parser(fn (string $s) => concat($this->run($s), $another->run($s)));
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
        )->call(concatenate, $x, $xs);
    }

    public function sepBy1With(Parser $sep)
    {
        return for_(
            __($x)->_($this),
            __($xs)->_(many(for_(
                __($s)->_($sep),
                __($y)->_($this)
            )->call(concatenate, $s, $y)))
        )->call(concatenate, $x, $xs);
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
