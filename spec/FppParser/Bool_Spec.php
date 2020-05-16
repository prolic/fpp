<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppSpec\FppParser;

use Fpp\Type\Bool_\Bool_;
use function Fpp\Type\Bool_\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('bool_', function () {
            it('can parse bool types', function () {
                expect(parse()->run('bool Truth;')->head()->_1)->toEqual(
                    new Bool_(
                        'Truth',
                        Nil()
                    )
                );
            });

            it('can parse bool types with markers', function () {
                expect(parse()->run('bool Truth : Foo, Bar;')->head()->_1)->toEqual(
                    new Bool_(
                        'Truth',
                        ImmList('Foo', 'Bar')
                    )
                );
            });
        });
    });
});
