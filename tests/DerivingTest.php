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
use Fpp\Condition;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\Deriving;
use Fpp\Deriving\Enum;
use Fpp\Deriving\FromScalar;
use Fpp\Deriving\Uuid;
use Fpp\InvalidDeriving;
use PHPUnit\Framework\TestCase;
use function Fpp\defaultDerivingMap;

class DerivingTest extends TestCase
{
    /**
     * @test
     */
    public function it_delivers_forbidden_derivings_and_to_string(): void
    {
        $derivingMap = defaultDerivingMap();

        foreach ($derivingMap as $name => $deriving) {
            $this->assertSame($name, (string) $deriving);
        }
    }

    /**
     * @test
     */
    public function from_scalar_requires_excatly_one_constructor(): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage('Invalid deriving on Foo\Bar, deriving FromScalar expects exactly one constructor');

        $fromScalar = new FromScalar();
        $fromScalar->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Bar'),
            new Constructor('Foo\Baz'),
        ]));
    }

    /**
     * @test
     */
    public function enum_requires_at_least_two_constructors(): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage('Invalid deriving on Foo\Bar, deriving Enum expects at least two constructors');

        $enum = new Enum();
        $enum->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Baz'),
        ]));
    }

    /**
     * @test
     */
    public function enum_requires_no_constructor_arguments(): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage('Invalid deriving on Foo\Bar, deriving Enum expects exactly zero constructor arguments');

        $enum = new Enum();
        $enum->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Baz', [
                new Argument('foo', 'string'),
            ]),
            new Constructor('Foo\Bam', [
                new Argument('foo', 'int'),
            ]),
        ]));
    }

    /**
     * @test
     */
    public function uuid_requires_no_constructor_arguments(): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage('Invalid deriving on Foo\Bar, deriving Uuid expects exactly zero constructor arguments');

        $uuid = new Uuid();
        $uuid->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Baz', [
                new Argument('foo', 'string'),
            ]),
        ]));
    }

    /**
     * @test
     * @dataProvider derivingsRequiringAtLeastOneConstructorArgument
     */
    public function deriving_expects_at_least_one_constructor_argument(Deriving $deriving): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid deriving on Foo\Bar, deriving %s expects at least one constructor argument',
            (string) $deriving
        ));

        $deriving->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Bar'),
        ]));
    }

    public function derivingsRequiringAtLeastOneConstructorArgument(): array
    {
        return [
            [
                new Deriving\AggregateChanged(),
            ],
            [
                new Deriving\DomainEvent(),
            ],
            [
                new Deriving\FromArray(),
            ],
            [
                new Deriving\MicroAggregateChanged(),
            ],
            [
                new Deriving\ToArray(),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider derivingsRequiringNoConditions
     */
    public function deriving_expects_no_conditions(Deriving $deriving): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid deriving on Foo\Bar, deriving %s expects no conditions at all',
            (string) $deriving
        ));

        $deriving->checkDefinition(new Definition('Foo', 'Bar', [
                new Constructor('Foo\Bar', [
                    new Argument('baz', 'Foo\Baz'),
                ]),
            ],
            [
            ],
            [
                new Condition('Foo\Bar', 'code', 'message'),
            ]
        ));
    }

    public function derivingsRequiringNoConditions(): array
    {
        return [
            [
                new Deriving\AggregateChanged(),
            ],
            [
                new Deriving\DomainEvent(),
            ],
            [
                new Deriving\Command(),
            ],
            [
                new Deriving\MicroAggregateChanged(),
            ],
            [
                new Deriving\Query(),
            ],
            [
                new Deriving\Uuid(),
            ],
        ];
    }

    /**
     * @test
     */
    public function enum_value_mapping_must_match_constructors(): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage('Invalid deriving on Foo\Bam, enum value mapping does not match constructors');

        $enum = new Deriving\Enum(['Foo\Bar' => 1, 'Foo\Baz' => 2]);
        $enum->checkDefinition(new Definition('Foo', 'Bam', [
            new Constructor('Foo\Bar'),
            new Constructor('Foo\Bag'),
        ]));
    }

    /**
     * @test
     * @dataProvider derivingsRequiringNoNullableOrListForFirstArgument
     */
    public function deriving_expects_no_nullable_first_argument(Deriving $deriving): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid first argument for Foo\Bar, %s deriving needs first argument to be no nullable and no list',
            (string) $deriving
        ));

        $deriving->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Bar', [
                new Argument('name', 'string', true),
            ]),
        ]));
    }

    /**
     * @test
     * @dataProvider derivingsRequiringNoNullableOrListForFirstArgument
     */
    public function deriving_expects_no_list_first_argument(Deriving $deriving): void
    {
        $this->expectException(InvalidDeriving::class);
        $this->expectExceptionMessage(sprintf(
            'Invalid first argument for Foo\Bar, %s deriving needs first argument to be no nullable and no list',
            (string) $deriving
        ));

        $deriving->checkDefinition(new Definition('Foo', 'Bar', [
            new Constructor('Foo\Bar', [
                new Argument('name', 'string', false, true),
            ]),
        ]));
    }

    public function derivingsRequiringNoNullableOrListForFirstArgument(): array
    {
        return [
            [
                new Deriving\AggregateChanged(),
            ],
            [
                new Deriving\FromString(),
            ],
            [
                new Deriving\MicroAggregateChanged(),
            ],
        ];
    }
}
