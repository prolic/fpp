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

use function Fpp\isKeyword;
use function Fpp\typeName;

describe("Fpp\Php", function () {
    context('Basic PHP parser functions', function () {
        describe('isKeyword', function () {
            it('returns true for any given php keyword', function () {
                expect(isKeyword('final'))->toBe(true);
            });

            it('returns false for any given non-keyword string', function () {
                expect(isKeyword('somestring'))->toBe(false);
            });

            it('returns false for an emptry string', function () {
                expect(isKeyword(''))->toBe(false);
            });
        });

        describe('typeName', function () {
            it('recognises type names out of a string', function () {
                expect(typeName()->run('Yes!'))
                    ->toEqual([
                        Pair('Yes', '!'),
                    ]);

                expect(typeName()->run('Yes2!'))
                    ->toEqual([
                        Pair('Yes2', '!'),
                    ]);

                expect(typeName()->run('Ye2s2!'))
                    ->toEqual([
                        Pair('Ye2s2', '!'),
                    ]);

                expect(typeName()->run('Ye_s2_!'))
                    ->toEqual([
                        Pair('Ye_s2_', '!'),
                    ]);

                expect(typeName()->run('_Y!'))
                    ->toEqual([
                        Pair('_Y', '!'),
                    ]);

                expect(typeName()->run('2Yes!'))
                    ->toEqual([]);
            });
        });
    });
});
