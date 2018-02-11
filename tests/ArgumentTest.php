<?php

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
        $argument = new Argument('', 'name', $dataProvider[0], false);
        $this->assertSame($dataProvider[1], $argument->isScalartypeHint());
    }

    public function dataProvider()
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
