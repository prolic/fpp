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
use Fpp\Type\Enum\Enum;
use function Fpp\Type\Enum\parse as enum;
use Phunkie\Types\ImmMap;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('multipleNamespaces', function () {
            it('cannot parse default namespace', function () {
                expect(multipleNamespaces(enum())->run('namespace { }'))->toEqual([]);
            });

            it('can parse namespace', function () {
                $testString = <<<FPP
namespace Foo {
    enum Color = Red | Green | Blue;
}
FPP;
                /** @var ImmMap $definition */
                $definition = multipleNamespaces(enum())->run($testString)[0]->_1;
                expect($definition->contains('Foo\Color'))->toBe(true);
                expect($definition->get('Foo\Color')->get()->imports())->toEqual([]);
            });

            it('can parse namespace with subnamespace', function () {
                $testString = <<<FPP
namespace Foo\Bar {
    enum Color = Red | Green | Blue;
}
FPP;
                /** @var ImmMap $definition */
                $definition = multipleNamespaces(enum())->run($testString)[0]->_1;
                expect($definition->contains('Foo\Bar\Color'))->toBe(true);
                expect($definition->get('Foo\Bar\Color')->get()->imports())->toEqual([]);
            });

            it('can parse namespace containing many enums', function () {
                $testString = <<<FPP
namespace Foo {
    enum Color = Red | Green | Blue;
    enum Human = Man | Woman;
}
FPP;
                /** @var ImmMap $definitions */
                $definitions = multipleNamespaces(enum())->run($testString)[0]->_1;
                expect($definitions->contains('Foo\Color'))->toBe(true);
                expect($definitions->contains('Foo\Human'))->toBe(true);

                expect($definitions->get('Foo\Color')->get()->imports())->toEqual([]);
                expect($definitions->get('Foo\Human')->get()->imports())->toEqual([]);
            });
        });
    });
});
