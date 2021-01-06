<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2021 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppSpec\FppParser;

use function Fpp\imports;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('imports', function () {
            it('can parse use imports', function () {
                expect(imports()->run('use Foo;')[0]->_1)->toEqual(Pair('Foo', null));
                expect(imports()->run("use Foo\Bar;")[0]->_1)->toEqual(Pair('Foo\Bar', null));
            });

            it('can parse aliased use imports', function () {
                expect(imports()->run('use Foo as F;')[0]->_1)->toEqual(Pair('Foo', 'F'));
                expect(imports()->run("use Foo\Bar as Baz;")[0]->_1)->toEqual(Pair('Foo\Bar', 'Baz'));
            });
        });
    });
});
