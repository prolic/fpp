<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppSpec;

use Fpp\Type\Enum\Constructor;
use Fpp\Type\EnumType;
use Phunkie\Types\Nil;

describe("Fpp\Type", function () {
    context('Fpp Type Objects', function () {
        describe('Enum', function () {
            it('creates a neat enum object', function () {
                $enum = new EnumType(
                    'Color',
                    \ImmList(
                        new Constructor('Red'),
                        new Constructor('Green'),
                        new Constructor('Blue')
                    )
                );

                expect($enum->className())->toBe('Color');
                expect($enum->constructors())->not->toBeAnInstanceOf(Nil::class);
            });
        });
    });
});
