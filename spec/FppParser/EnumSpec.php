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

use Fpp\Type\Enum\Constructor;
use Fpp\Type\Enum\Enum;
use function Fpp\Type\Enum\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('enum', function () {
            it('can parse enums', function () {
                expect(parse()->run('enum Color = Red | Green | Blue;')[0]->_1)->toEqual(
                    new Enum(
                        'Color',
                        [],
                        [
                            new Constructor('Red'),
                            new Constructor('Green'),
                            new Constructor('Blue'),
                        ]
                    )
                );
            });

            it('can parse enums with markers', function () {
                expect(parse()->run('enum Color : Enum = Red | Green | Blue;')[0]->_1)->toEqual(
                    new Enum(
                        'Color',
                        ['Enum'],
                        [
                            new Constructor('Red'),
                            new Constructor('Green'),
                            new Constructor('Blue'),
                        ]
                    )
                );
            });

            it('cannot parse enum constructors without semicolon ending', function () {
                expect(parse()->run('enum Color = Red | Green | Blue'))->toEqual([]);
            });
        });
    });
});
