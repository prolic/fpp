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

use function Fpp\float_;
use Fpp\Type\FloatType;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('float_', function () {
            it('can parse float types', function () {
                $testString = <<<CODE
float Longitude;
CODE;

                expect(float_()->run($testString)->head()->_1)->toEqual(
                    new FloatType(
                        'Longitude'
                    )
                );
            });
        });
    });
});
