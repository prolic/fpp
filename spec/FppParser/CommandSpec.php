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

use Fpp\Argument;
use Fpp\Type\Command\Command;
use Fpp\Type\Command\Constructor;
use function Fpp\Type\Command\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('command', function () {
            it('can parse command types', function () {
                expect(parse()->run('command Cmd (Guid) = Go { string, int };')[0]->_1)->toEqual(
                    new Command(
                        'Cmd',
                        [],
                        'Guid',
                        [
                            new Constructor(
                                'Go',
                                '',
                                [
                                    new Argument(
                                        'string',
                                        'string',
                                        false,
                                        false,
                                        null),
                                    new Argument(
                                        'int',
                                        'int',
                                        false,
                                        false,
                                        null
                                    ),
                                ]
                            ),
                        ]
                    )
                );
            });

            it('can parse command types with custom names', function () {
                expect(parse()->run('command Cmd (Guid) = Go as goo { string, int };')[0]->_1)->toEqual(
                    new Command(
                        'Cmd',
                        [],
                        'Guid',
                        [
                            new Constructor(
                                'Go',
                                'goo',
                                [
                                    new Argument(
                                        'string',
                                        'string',
                                        false,
                                        false,
                                        null
                                    ),
                                    new Argument(
                                        'int',
                                        'int',
                                        false,
                                        false,
                                        null
                                    ),
                                ]
                            ),
                        ]
                    )
                );
            });

            it('can parse command types with custom name, marker and multiple types', function () {
                expect(parse()->run('command Cmd : Marker (Guid) = Go as goo { string, int }
                    | Go2 as goo2 { bool, float };')[0]->_1)->toEqual(
                    new Command(
                        'Cmd',
                        ['Marker'],
                        'Guid',
                        [
                            new Constructor(
                                'Go',
                                'goo',
                                [
                                    new Argument(
                                        'string',
                                        'string',
                                        false,
                                        false,
                                        null
                                    ),
                                    new Argument(
                                        'int',
                                        'int',
                                        false,
                                        false,
                                        null
                                    ),
                                ]
                            ),
                            new Constructor(
                                'Go2',
                                'goo2',
                                [
                                    new Argument(
                                        'bool',
                                        'bool',
                                        false,
                                        false,
                                        null
                                    ),
                                    new Argument(
                                        'float',
                                        'float',
                                        false,
                                        false,
                                        null
                                    ),
                                ]
                            ),
                        ]
                    )
                );
            });
        });
    });
});
