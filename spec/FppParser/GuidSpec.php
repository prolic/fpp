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

use Fpp\Type\Guid\Guid;
use function Fpp\Type\Guid\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('guid', function () {
            it('can parse guid types', function () {
                expect(parse()->run('guid UserId;')->head()->_1)->toEqual(
                    new Guid('UserId', Nil())
                );
            });

            it('can parse guid types with markers', function () {
                expect(parse()->run('guid UserId : Stringify;')->head()->_1)->toEqual(
                    new Guid('UserId', ImmList('Stringify'))
                );
            });
        });
    });
});
