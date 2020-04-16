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
use function Fpp\singleNamespace;
use Fpp\Type;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('singleNamespace', function () {
            it('can parse one namespace when ending with ;', function () {
                expect(singleNamespace(enum())->run("namespace Foo\n")->head()->_1)->toEqual(
                    new Type\NamespaceType('Foo', Nil(), Nil())
                );
            });

            it('cannot parse second namespace when ending with ;', function () {
                expect(singleNamespace(enum())->run("namespace Foo\nnamespace Bar\n")->head()->_2)->toBe("namespace Bar\n");
            });

            it('can parse one namespace when ending with ; with an enum inside', function () {
                expect(singleNamespace(enum())->run("namespace Foo\nenum Color = Red | Blue\n")->head()->_1)->toEqual(
                    new Type\NamespaceType('Foo', Nil(), ImmList(
                        new Type\EnumType(
                            'Color',
                            ImmList(
                                new Type\Enum\Constructor('Red'),
                                new Type\Enum\Constructor('Blue')
                            )
                        )
                    ))
                );
            });

            it('can parse one namespace when ending with ; with use imports and an enum inside', function () {
                $testString = <<<CODE
namespace Foo
use Foo\Bar
use Foo\Baz as B
enum Color = Red | Blue

CODE;

                expect(singleNamespace(enum())->run($testString)->head()->_1)->toEqual(
                    new Type\NamespaceType(
                        'Foo',
                        ImmList(
                            Pair('Foo\Bar', null),
                            Pair('Foo\Baz', 'B')
                        ),
                        ImmList(
                            new Type\EnumType(
                                'Color',
                                ImmList(
                                    new Type\Enum\Constructor('Red'),
                                    new Type\Enum\Constructor('Blue')
                                )
                            )
                        )
                    )
                );
            });
        });
    });
});
