<?php
/**
 * This file is part of prolic/fpp.
 * (c) 2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FppTest\Builder;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildStaticConstructorBody;

class BuildStaticConstructorBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload_without_first_argument(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            DefinitionType::data(),
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\AggregateChanged()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
return new self(\$id->toString(), [
            'name' => \$name,
            'email' => \$email->toString(),
            'something' => \$something,
        ]);
CODE;

        $this->assertSame($expected, buildStaticConstructorBody($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload_with_first_argument(): void
    {
        $userId = new Definition(
            DefinitionType::data(),
            'My',
            'UserId',
            [
                new Constructor('My\UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            DefinitionType::data(),
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $constructor = new Constructor('My\UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'UserRegistered',
            [$constructor],
            [new Deriving\Command()]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
return new self([
            'id' => \$id->toString(),
            'name' => \$name,
            'email' => \$email->toString(),
            'something' => \$something,
        ]);
CODE;

        $this->assertSame($expected, buildStaticConstructorBody($definition, $constructor, $collection, ''));
    }

    /**
     * @test
     */
    public function it_builds_static_constructor_body_converting_to_payload_with_enums(): void
    {
        $constructor1 = new Constructor('My\Red');
        $constructor2 = new Constructor('My\Blue');

        $simpleColor = new Definition(
            DefinitionType::data(),
            'My',
            'SimpleColor',
            [$constructor1, $constructor2],
            [new Deriving\Enum()]
        );


        $constructor1 = new Constructor('My\RED');
        $constructor2 = new Constructor('My\VERY_RED');

        $color = new Definition(
            DefinitionType::data(),
            'My',
            'Color',
            [$constructor1, $constructor2],
            [new Deriving\Enum(
                [],
                ['useValue']
            )]
        );

        $constructor = new Constructor('My\Person', [
            new Argument('simpleColors', 'My\SimpleColor', false, false),
            new Argument('simpleColorsNullable', 'My\SimpleColor', true, false),
            new Argument('colors', 'My\Color', false, false),
            new Argument('colorsNullable', 'My\Color', true, false),
        ]);

        $definition = new Definition(
            DefinitionType::data(),
            'My',
            'Person',
            [$constructor],
            [new Deriving\Command()]
        );
        $collection = new DefinitionCollection($simpleColor, $color, $definition);

        $expected = <<<CODE
return new self([
            'simpleColors' => \$simpleColors->name(),
            'simpleColorsNullable' => null === \$simpleColorsNullable ? null : \$simpleColorsNullable->name(),
            'colors' => \$colors->value(),
            'colorsNullable' => null === \$colorsNullable ? null : \$colorsNullable->value(),
        ]);
CODE;

        $this->assertSame($expected, buildStaticConstructorBody($definition, $constructor, $collection, ''));
    }
}
