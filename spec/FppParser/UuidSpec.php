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

use function Fpp\Type\Uuid\parse;
use Fpp\Type\Uuid\Uuid;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('uuid', function () {
            it('can parse uuid types', function () {
                expect(parse()->run('uuid UserId;')[0]->_1)->toEqual(
                    new Uuid('UserId', [])
                );
            });

            it('can parse guid types with markers', function () {
                expect(parse()->run('uuid UserId : Stringify;')[0]->_1)->toEqual(
                    new Uuid('UserId', ['Stringify'])
                );
            });
        });
    });
});
