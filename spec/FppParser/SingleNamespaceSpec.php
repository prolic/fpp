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

use function Fpp\singleNamespace;
use Fpp\Type\Enum\Enum;
use function Fpp\Type\Enum\parse as enum;
use Phunkie\Types\ImmMap;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('singleNamespace', function () {
            it('cannot parse second namespace when ending with ;', function () {
                expect(singleNamespace(enum())->run('namespace Foo;namespace Bar;')->head()->_2)->toBe('namespace Bar;');
            });

            it('can parse one namespace when ending with ;', function () {
                $testString = <<<CODE
namespace Foo;
enum Color = Red | Blue;
CODE;

                /** @var ImmMap $definition */
                $definition = singleNamespace(enum())->run($testString)->head()->_1;
                expect($definition->contains('Foo\Color'))->toBe(true);
                expect($definition->get('Foo\Color')->get()->imports())->toEqual(Nil());
            });

            it('can parse one namespace when ending with ; with use imports and an enum inside', function () {
                $testString = <<<CODE
namespace Foo;
use Foo\Bar;
use Foo\Baz as B;
enum Color = Red | Blue;
CODE;

                /** @var ImmMap $definition */
                $definition = singleNamespace(enum())->run($testString)->head()->_1;
                expect($definition->contains('Foo\Color'))->toBe(true);
                expect($definition->get('Foo\Color')->get()->imports())->toEqual(ImmList(
                    Pair('Foo\Bar', null),
                    Pair('Foo\Baz', 'B')
                ));
            });
        });
    });
});
