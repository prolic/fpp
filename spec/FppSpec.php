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

use function Fpp\flatMap;
use function Fpp\isKeyword;

describe('Fpp', function () {
    context('Basic FPP helper functions', function () {
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

        describe('flatMap', function () {
            it('flattens an array', function () {
                $fn = function ($x) {
                    if (\is_array($x)) {
                        $res = [];

                        foreach ($x as $y) {
                            $res[] = $y + 1;
                        }

                        return $res;
                    }

                    return $x + 1;
                };

                $input = [1, 2, [3, 4]];

                expect(flatMap($fn, $input))->toBe([2, 3, 4, 5]);
            });
        });
    });
});
