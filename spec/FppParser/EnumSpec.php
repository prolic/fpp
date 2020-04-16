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

use function Fpp\enum;
use Fpp\Type;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('enum', function () {
            it('can parse enums', function () {
                expect(enum()->run("enum Color = Red | Green | Blue\n")->head()->_1)->toEqual(
                    new Type\EnumType(
                        'Color',
                        ImmList(
                            new Type\Enum\Constructor('Red'),
                            new Type\Enum\Constructor('Green'),
                            new Type\Enum\Constructor('Blue')
                        )
                    )
                );
            });

            it('cannot parse enum constructors without new line ending', function () {
                expect(enum()->run('enum Color = Red | Green | Blue'))->toEqual(Nil());
            });
        });
    });
});
