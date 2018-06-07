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

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionType;
use Fpp\DefinitionCollection;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildClassKeyword;

class BuildClassKeywordTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_abstract_keyword(): void
    {
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Color', [new Constructor('Foo\Red')]);

        $this->assertSame('abstract ', buildClassKeyword($definition, null, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_adds_final_keyword(): void
    {
        $constructor = new Constructor('Foo\Red');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Color', [$constructor]);

        $this->assertSame('final ', buildClassKeyword($definition, $constructor, new DefinitionCollection($definition), ''));
    }

    /**
     * @test
     */
    public function it_adds_no_keyword(): void
    {
        $constructor = new Constructor('Foo\Bar');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Bar', [$constructor, new Constructor('Foo\Baz')]);

        $this->assertSame('', buildClassKeyword($definition, $constructor, new DefinitionCollection($definition), ''));
    }
}
