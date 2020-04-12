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

use function Fpp\constructorSeparator;
use function Fpp\enum;
use function Fpp\enumConstructors;
use function Fpp\namespaceName;
use Fpp\Type\Enum\Constructor;
use Fpp\Type\EnumType;
use Fpp\Type\NamespaceType;
use function Fpp\typeName;
use Phunkie\Types\ImmList;
use Phunkie\Types\Pair;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('typeName', function () {
            it('recognises type names out of a string', function () {
                expect(typeName()->run('Yes!'))
                    ->toEqual(ImmList(
                        Pair('Yes', '!'),
                        Pair('Ye', 's!'),
                        Pair('Y', 'es!')
                    ));

                expect(typeName()->run('Yes2!'))
                    ->toEqual(ImmList(
                        Pair('Yes2', '!'),
                        Pair('Yes', '2!'),
                        Pair('Ye', 's2!'),
                        Pair('Y', 'es2!')
                    ));

                expect(typeName()->run('Ye2s2!'))
                    ->toEqual(ImmList(
                        Pair('Ye2s2', '!'),
                        Pair('Ye2s', '2!'),
                        Pair('Ye2', 's2!'),
                        Pair('Ye', '2s2!'),
                        Pair('Y', 'e2s2!')
                    ));

                expect(typeName()->run('Ye_s2_!'))
                    ->toEqual(ImmList(
                        Pair('Ye_s2_', '!'),
                        Pair('Ye_s2', '_!'),
                        Pair('Ye_s', '2_!'),
                        Pair('Ye_', 's2_!'),
                        Pair('Ye', '_s2_!'),
                        Pair('Y', 'e_s2_!')
                    ));

                expect(typeName()->run('2Yes!'))
                    ->toEqual(Nil());
            });
        });

        describe('constructorSeparator', function () {
            it('can parse constructor separators', function () {
                expect(constructorSeparator()->run('|')->head()->_1)->toBe('|');

                expect(constructorSeparator()->run(' | ')->head()->_1)->toBe('|');

                expect(constructorSeparator()->run('  |   ')->head()->_1)->toBe('|');
            });
        });

        describe('enumConstructors', function () {
            it('can parse enum constructors', function () {
                expect(enumConstructors()->run("Red|Green|Blue\n")->head()->_1)->toEqual(ImmList(
                    new Constructor('Red'),
                    new Constructor('Green'),
                    new Constructor('Blue')
                ));

                expect(enumConstructors()->run("Red|Green|Blue\n")->head()->_1)->toEqual(ImmList(
                    new Constructor('Red'),
                    new Constructor('Green'),
                    new Constructor('Blue')
                ));
            });

            it('cannot parse enum constructors without new line ending', function () {
                expect(enumConstructors()->run('Red|Green|Blue'))->toEqual(Nil());
            });
        });

        describe('enum', function () {
            it('can parse enums', function () {
                expect(enum()->run("enum Color = Red | Green | Blue\n")->head()->_1)->toEqual(
                    new EnumType(
                        'Color',
                        ImmList(
                            new Constructor('Red'),
                            new Constructor('Green'),
                            new Constructor('Blue')
                        )
                    )
                );
            });
        });

        describe('namespace', function () {
            it('can parse empty namespace', function () {
                expect(namespaceName(enum())->run('namespace Foo { }')->head()->_1)->toEqual(
                    new NamespaceType('Foo', Nil())
                );
            });

            it('can parse namespace with sub namespace', function () {
                expect(namespaceName(enum())->run('namespace Foo\Bar { }')->head()->_1)->toEqual(
                    new NamespaceType('Foo\Bar', Nil())
                );
            });

            it('can parse namespace containing an enum', function () {
                $testString = <<<FPP
namespace Foo {
    enum Color = Red | Green | Blue
}
FPP;
                expect(namespaceName(enum())->run($testString)->head()->_1)->toEqual(
                    new NamespaceType('Foo', ImmList(
                        new EnumType(
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
    enum Color = Red | Green | Blue
    enum Human = Man | Woman
}
FPP;
                expect(namespaceName(enum())->run($testString)->head()->_1)->toEqual(
                    new NamespaceType('Foo', ImmList(
                        new EnumType(
                            'Color',
                            ImmList(
                                new Constructor('Red'),
                                new Constructor('Green'),
                                new Constructor('Blue')
                            )
                        ),
                        new EnumType(
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
