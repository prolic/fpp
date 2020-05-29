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

use Fpp\Type\Marker\Marker;
use function Fpp\Type\Marker\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('marker', function () {
            it('can parse marker types', function () {
                expect(parse()->run('marker Foo;')[0]->_1)->toEqual(
                    new Marker(
                        'Foo',
                        []
                    )
                );
            });

            it('can parse marker types extending another marker', function () {
                expect(parse()->run('marker Foo : Bar;')[0]->_1)->toEqual(
                    new Marker(
                        'Foo',
                        ['Bar']
                    )
                );
            });

            it('can parse marker types extending multiple markers', function () {
                expect(parse()->run('marker Foo : Bar, Baz, Bam;')[0]->_1)->toEqual(
                    new Marker(
                        'Foo',
                        ['Bar', 'Baz', 'Bam']
                    )
                );
            });
        });
    });
});
