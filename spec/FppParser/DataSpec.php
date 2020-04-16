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
        });
    });
});
