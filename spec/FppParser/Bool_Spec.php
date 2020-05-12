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

use function Fpp\bool_;
use Fpp\Type\BoolType;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('bool_', function () {
            it('can parse bool types', function () {
                $testString = <<<CODE
bool Truth;
CODE;

                expect(bool_()->run($testString)->head()->_1)->toEqual(
                    new BoolType(
                        'Truth'
                    )
                );
            });
        });
    });
});
