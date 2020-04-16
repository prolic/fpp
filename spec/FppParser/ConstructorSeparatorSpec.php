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

use function Fpp\constructorSeparator;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('constructorSeparator', function () {
            it('can parse constructor separators', function () {
                expect(constructorSeparator()->run('|')->head()->_1)->toBe('|');
                expect(constructorSeparator()->run(' | ')->head()->_1)->toBe('|');
                expect(constructorSeparator()->run('  |   ')->head()->_1)->toBe('|');
            });
        });
    });
});
