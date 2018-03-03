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
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildToScalarBody;

class BuildToScalarBodyTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_to_scalar_body(): void
    {
        $constructor1 = new Constructor('Int');

        $userId = new Definition(
            'My',
            'UserId',
            [$constructor1],
            [new Deriving\ToScalar()]
        );

        $constructor2 = new Constructor('String');

        $email = new Definition(
            'Some',
            'Email',
            [$constructor2],
            [new Deriving\ToScalar()]
        );

        $constructor3 = new Constructor('My\Email', [
            new Argument('key', 'string'),
        ]);

        $definition = new Definition(
            'My',
            'Email',
            [$constructor3],
            [new Deriving\ToScalar()]
        );

        $expected = 'return $this->key;';

        $this->assertSame($expected, buildToScalarBody($definition, $constructor3, new DefinitionCollection($definition, $userId, $email), ''));

        $expected = 'return $this->value;';

        $this->assertSame($expected, buildToScalarBody($definition, $constructor2, new DefinitionCollection($definition, $userId, $email), ''));

        $expected = 'return $this->value;';

        $this->assertSame($expected, buildToScalarBody($definition, $constructor1, new DefinitionCollection($definition, $userId, $email), ''));
    }
}
