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

use Fpp\Namespace_;
use function Fpp\singleNamespace;
use Fpp\Type\Enum\Constructor;
use Fpp\Type\Enum\Enum;
use function Fpp\Type\Enum\parse as enum;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('singleNamespace', function () {
            it('can parse one namespace when ending with ;', function () {
                expect(singleNamespace(enum())->run('namespace Foo;')->head()->_1)->toEqual(
                    new Namespace_('Foo', Nil(), Nil())
                );
            });

            it('cannot parse second namespace when ending with ;', function () {
                expect(singleNamespace(enum())->run('namespace Foo;namespace Bar;')->head()->_2)->toBe('namespace Bar;');
            });

            it('can parse one namespace when ending with ; with an enum inside', function () {
                $testString = <<<CODE
namespace Foo;
enum Color = Red | Blue;
CODE;

                expect(singleNamespace(enum())->run($testString)->head()->_1)->toEqual(
                    new Namespace_('Foo', Nil(), ImmList(
                        new Enum(
                            'Color',
                            ImmList(
                                new Constructor('Red'),
                                new Constructor('Blue')
                            )
                        )
                    ))
                );
            });

            it('can parse one namespace when ending with ; with use imports and an enum inside', function () {
                $testString = <<<CODE
namespace Foo;
use Foo\Bar;
use Foo\Baz as B;
enum Color = Red | Blue;
CODE;

                expect(singleNamespace(enum())->run($testString)->head()->_1)->toEqual(
                    new Namespace_(
                        'Foo',
                        ImmList(
                            Pair('Foo\Bar', null),
                            Pair('Foo\Baz', 'B')
                        ),
                        ImmList(
                            new Enum(
                                'Color',
                                ImmList(
                                    new Constructor('Red'),
                                    new Constructor('Blue')
                                )
                            )
                        )
                    )
                );
            });
        });
    });
});
