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

use function Fpp\multipleNamespaces;
use Fpp\Namespace_;
use Fpp\Type\Enum\Constructor;
use Fpp\Type\Enum\Enum;
use function Fpp\Type\Enum\parse as enum;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('multipleNamespaces', function () {
            it('can parse empty namespace', function () {
                expect(multipleNamespaces(enum())->run('namespace Foo { }')->head()->_1)->toEqual(
                    new Namespace_('Foo', Nil(), Nil())
                );
            });

            it('can parse namespace with sub namespace', function () {
                expect(multipleNamespaces(enum())->run('namespace Foo\Bar { }')->head()->_1)->toEqual(
                    new Namespace_('Foo\Bar', Nil(), Nil())
                );
            });

            it('cannot parse default namespace', function () {
                expect(multipleNamespaces(enum())->run('namespace { }'))->toEqual(Nil());
            });

            it('can parse namespace containing an enum', function () {
                $testString = <<<FPP
namespace Foo {
    enum Color = Red | Green | Blue;
}
FPP;
                expect(multipleNamespaces(enum())->run($testString)->head()->_1)->toEqual(
                    new Namespace_('Foo', Nil(), ImmList(
                        new Enum(
                            'Color',
                            ImmList(
                                new Constructor('Red'),
                                new Constructor('Green'),
                                new Constructor('Blue')
                            )
                        )
                    ))
                );
            });

            it('can parse namespace containing many enums', function () {
                $testString = <<<FPP
namespace Foo {
    enum Color = Red | Green | Blue;
    enum Human = Man | Woman;
}
FPP;
                expect(multipleNamespaces(enum())->run($testString)->head()->_1)->toEqual(
                    new Namespace_('Foo', Nil(), ImmList(
                        new Enum(
                            'Color',
                            ImmList(
                                new Constructor('Red'),
                                new Constructor('Green'),
                                new Constructor('Blue')
                            )
                        ),
                        new Enum(
                            'Human',
                            ImmList(
                                new Constructor('Man'),
                                new Constructor('Woman')
                            )
                        )
                    ))
                );
            });
        });
    });
});
