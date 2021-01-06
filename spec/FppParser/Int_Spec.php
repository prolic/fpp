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

use Fpp\Type\Int_\Int_;
use function Fpp\Type\Int_\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('int_', function () {
            it('can parse int types', function () {
                expect(parse()->run('int Age;')[0]->_1)->toEqual(
                    new Int_(
                        'Age',
                        []
                    )
                );
            });

            it('can parse int types with markers', function () {
                expect(parse()->run('int Age : Number;')[0]->_1)->toEqual(
                    new Int_(
                        'Age',
                        ['Number']
                    )
                );
            });
        });
    });
});
