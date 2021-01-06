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

use function Fpp\Type\String_\parse;
use Fpp\Type\String_\String_;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('string_', function () {
            it('can parse string types', function () {
                expect(parse()->run('string Username;')[0]->_1)->toEqual(
                    new String_(
                        'Username',
                        []
                    )
                );
            });

            it('can parse string types with markers', function () {
                expect(parse()->run('string Username : LetterCollection;')[0]->_1)->toEqual(
                    new String_(
                        'Username',
                        ['LetterCollection']
                    )
                );
            });
        });
    });
});
