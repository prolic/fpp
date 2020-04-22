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

use function Fpp\data;
use Fpp\Type;
use Fpp\Type\DataType;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('data', function () {
            it('can parse simple data types', function () {
                $testString = <<<CODE
data Person = { \$name, \$age}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', null, false, false, null),
                            new Type\Data\Argument('age', null, false, false, null),
                        )
                    )
                );
            });

            it('can parse simple data types with scalar types', function () {
                $testString = <<<CODE
data Person = { string \$name, int \$age}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', 'string', false, false, null),
                            new Type\Data\Argument('age', 'int', false, false, null),
                        )
                    )
                );
            });

            it('can parse data types with nullable argument', function () {
                $testString = <<<CODE
data Person = { ?string \$name, int \$age}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', 'string', true, false, null),
                            new Type\Data\Argument('age', 'int', false, false, null),
                        )
                    )
                );
            });

            it('can parse data types with two nullable arguments', function () {
                $testString = <<<CODE
data Person = { ?string \$name, ?int \$age}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', 'string', true, false, null),
                            new Type\Data\Argument('age', 'int', true, false, null),
                        )
                    )
                );
            });

            it('can parse data types with default value argument', function () {
                $testString = <<<CODE
data Person = { string \$name = 'prooph', int \$age = 18}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', 'string', false, false, '\'prooph\''),
                            new Type\Data\Argument('age', 'int', false, false, 18),
                        )
                    )
                );
            });

            it('can parse data types with default value argument and nullable', function () {
                $testString = <<<CODE
data Person = { string \$name, ?int \$age = null}

CODE;

                expect(data()->run($testString)->head()->_1)->toEqual(
                    new DataType(
                        'Person',
                        ImmList(
                            new Type\Data\Argument('name', 'string', false, false, null),
                            new Type\Data\Argument('age', 'int', true, false, 'null'),
                        )
                    )
                );
            });
        });
    });
});
