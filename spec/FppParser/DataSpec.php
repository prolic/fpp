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

use Fpp\Argument;
use Fpp\Type\Data\Constructor;
use Fpp\Type\Data\Data;
use function Fpp\Type\Data\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('data', function () {
            it('can parse simple data types', function () {
                expect(parse()->run('data Person = { $name, $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', null, false, false, null),
                                new Argument('age', null, false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse simple data types with scalar types', function () {
                expect(parse()->run('data Person = { string $name, int $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'string', false, false, null),
                                new Argument('age', 'int', false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse simple data types with types', function () {
                expect(parse()->run('data Person = { Name $name, Age $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'Name', false, false, null),
                                new Argument('age', 'Age', false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types with nullable argument', function () {
                expect(parse()->run('data Person = { ?string $name, int $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'string', true, false, null),
                                new Argument('age', 'int', false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types with two nullable arguments', function () {
                expect(parse()->run('data Person = { ?string $name, ?int $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'string', true, false, null),
                                new Argument('age', 'int', true, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types with default value argument', function () {
                expect(parse()->run('data Person = { string $name = \'prooph\', int $age = 18};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'string', false, false, '\'prooph\''),
                                new Argument('age', 'int', false, false, 18),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types with default value argument and nullable', function () {
                expect(parse()->run('data Person = { string $name, ?int $age = null};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        [],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'string', false, false, null),
                                new Argument('age', 'int', true, false, 'null'),
                            ]),
                        ]
                    )
                );
            });

            it('can parse simple data types with markers', function () {
                expect(parse()->run('data Person : Human = { Name $name, Age $age};')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        ['Human'],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'Name', false, false, null),
                                new Argument('age', 'Age', false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types without variable names', function () {
                expect(parse()->run('data Person : Human = { Name, Age };')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        ['Human'],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'Name', false, false, null),
                                new Argument('age', 'Age', false, false, null),
                            ]),
                        ]
                    )
                );
            });

            it('can parse data types without variable names and repeating types', function () {
                expect(parse()->run('data Person : Human = { Name, Name };')[0]->_1)->toEqual(
                    new Data(
                        'Person',
                        ['Human'],
                        [
                            new Constructor('Person', [
                                new Argument('name', 'Name', false, false, null),
                                new Argument('name2', 'Name', false, false, null),
                            ]),
                        ]
                    )
                );
            });
        });
    });
});
