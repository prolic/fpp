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

use function Fpp\marker;
use Fpp\Type\MarkerType;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('marker', function () {
            it('can parse marker types', function () {
                $testString = <<<CODE
marker Foo

CODE;

                expect(marker()->run($testString)->head()->_1)->toEqual(
                    new MarkerType(
                        'Foo',
                        Nil()
                    )
                );
            });
        });
    });
});
