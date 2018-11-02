<?php

/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest;

use Fpp\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_checks_scalar_type_hints(array $dataProvider): void
    {
        $argument = new Argument('name', $dataProvider[0], false);
        $this->assertSame($dataProvider[1], $argument->isScalartypeHint());
    }

    public function dataProvider(): array
    {
        return [
            [
                [
                    'string',
                    true,
                ],
            ],
            [
                [
                    'int',
                    true,
                ],
            ],
            [
                [
                    'bool',
                    true,
                ],
            ],
            [
                [
                    'float',
                    true,
                ],
            ],
            [
                [
                    'Person',
                    false,
                ],
            ],
        ];
    }
}
