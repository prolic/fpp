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
use Fpp\Type\Event\Constructor;
use Fpp\Type\Event\Event;
use function Fpp\Type\Event\parse;

describe("Fpp\Parser", function () {
    context('FPP parsers', function () {
        describe('event', function () {
            it('can parse event types', function () {
                expect(parse()->run('event Ev (Guid, int) = Go { string, int };')[0]->_1)->toEqual(
                    new Event(
                        'Ev',
                        [],
                        'Guid',
                        'int',
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

            it('can parse event types with custom names', function () {
                expect(parse()->run('event Ev (Guid, int) = Go as goo { string, int };')[0]->_1)->toEqual(
                    new Event(
                        'Ev',
                        [],
                        'Guid',
                        'int',
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

            it('can parse event types with custom name, marker and multiple types', function () {
                expect(parse()->run('event Ev : Marker (Guid, int) = Go as goo { string, int }
                    | Go2 as goo2 { bool, float };')[0]->_1)->toEqual(
                    new Event(
                        'Ev',
                        ['Marker'],
                        'Guid',
                        'int',
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
