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
use Fpp\Type\Enum;
use Fpp\Type\NamespaceType;
use const Phunkie\Functions\immlist\concat;

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
        __($xs)->_(many(plus(alphanum(), char('_'))))
    )->call(concat, $x, $xs);
}

function constructorSeparator(): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($s)->_(char('|')),
        __($_)->_(spaces())
    )->yields($s);
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

const namespaceName = 'Fpp\namespaceName';

function namespaceName(Parser $parsers): Parser
{
    return for_(
        __($_)->_(spaces()),
        __($_)->_(string('namespace')),
        __($_)->_(spaces1()),
        __($n)->_(sepBy1With(typeName(), char('\\'))),
        __($cs)->_(surrounded(
            for_(
                __($_)->_(spaces()),
                __($o)->_(char('{')),
                __($_)->_(spaces())
            )->yields($o),
            manyList($parsers),
            for_(
                __($_)->_(spaces()),
                __($c)->_(char('}')),
                __($_)->_(spaces())
            )->yields($c)
        ))
    )->call(fn ($n, $cs) => new NamespaceType($n, $cs), $n, $cs);
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
    )->call(fn ($t, $cs) => new Enum($t, $cs), $t, $cs);
}
