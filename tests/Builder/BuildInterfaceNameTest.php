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
use Fpp\DefinitionCollection;
use Fpp\DefinitionType;
use PHPUnit\Framework\TestCase;
use function Fpp\Builder\buildInterfaceName;

class BuildInterfaceNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_interface_name(): void
    {
        $definition = new Definition(DefinitionType::marker(), 'Foo', 'Exception');

        $this->assertSame('Exception', buildInterfaceName($definition, null, new DefinitionCollection($definition), '{{ interface_name }}'));
    }

    /**
     * @test
     */
    public function it_builds_lowercased_interface_name(): void
    {
        $definition = new Definition(DefinitionType::marker(), 'Foo', 'exception');

        $this->assertSame('Exception', buildInterfaceName($definition, null, new DefinitionCollection($definition), '{{ interface_name }}'));
    }

    /**
     * @test
     */
    public function it_does_not_build_data_definition()
    {
        $definition = new Definition(DefinitionType::data(), 'Foo', 'FirstName', [new Constructor('String')]);

        $this->assertSame('{{ interface_name }}', buildInterfaceName($definition, null, new DefinitionCollection($definition), '{{ interface_name }}'));
    }
}
