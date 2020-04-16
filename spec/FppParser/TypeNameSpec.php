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

use function Fpp\typeName;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('typeName', function () {
            it('recognises type names out of a string', function () {
                expect(typeName()->run('Yes!')->head()->_1)->toEqual('Yes');
                expect(typeName()->run('Yes2!')->head()->_1)->toEqual('Yes2');
                expect(typeName()->run('Ye2s2!')->head()->_1)->toEqual('Ye2s2');
                expect(typeName()->run('Ye_s2_!')->head()->_1)->toEqual('Ye_s2_');
                expect(typeName()->run('2Yes!'))->toEqual(Nil());
            });

            it('cannot parse php keyword as type name', function () {
                expect(typeName()->run('Public'))->toEqual(ImmList(
                    Pair('Publi', 'c'),
                    Pair('Publ', 'ic'),
                    Pair('Pub', 'lic'),
                    Pair('Pu', 'blic'),
                    Pair('P', 'ublic'),
                ));
            });

            it('can parse strings containing a php keyword', function () {
                expect(typeName()->run('Publics')->head()->_1)->toBe('Publics');
                expect(typeName()->run('   Final_')->head()->_1)->toBe('Final_');
            });
        });
    });
});
