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

use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionType;
use PHPUnit\Framework\TestCase;
use function Fpp\locatePsrPath;

class LocatePsrPathTest extends TestCase
{
    private $prefixesPsr4 = [
        'Foo\\' => ['/var/Foo'],
    ];

    private $prefixesPsr0 = [
        'Bar\\' => ['/var'],
    ];

    /**
     * @test
     */
    public function it_locates_psr4_path_from_constructor(): void
    {
        $constructor = new Constructor('Foo\Person');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor]);

        $this->assertSame('/var/Foo/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, $constructor));
    }

    /**
     * @test
     */
    public function it_locates_psr0_path_from_constructor(): void
    {
        $constructor = new Constructor('Bar\Person');
        $definition = new Definition(DefinitionType::data(), 'Bar', 'Person', [$constructor]);

        $this->assertSame('/var/Bar/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, $constructor));
    }

    /**
     * @test
     */
    public function it_locates_psr4_path_from_definition(): void
    {
        $constructor = new Constructor('Foo\Person');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor]);

        $this->assertSame('/var/Foo/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, null));
    }

    /**
     * @test
     */
    public function it_locates_psr0_path_from_definition(): void
    {
        $constructor = new Constructor('Bar\Person');
        $definition = new Definition(DefinitionType::data(), 'Bar', 'Person', [$constructor]);

        $this->assertSame('/var/Bar/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, null));
    }

    /**
     * @test
     */
    public function it_throws_when_unknown_prefix_requested(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find psr-autoloading path for Unknown\Person');

        $constructor = new Constructor('Unknown\Person');
        $definition = new Definition(DefinitionType::data(), 'Unknown', 'Person', [$constructor]);

        locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, null);
    }

    /**
     * @test
     */
    public function it_locates_psr4_path_from_definition_when_scalar_constructor_given(): void
    {
        $constructor = new Constructor('String');
        $definition = new Definition(DefinitionType::data(), 'Foo', 'Person', [$constructor]);

        $this->assertSame('/var/Foo/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, $constructor));
    }

    /**
     * @test
     */
    public function it_locates_psr0_path_from_definition_when_scalar_constructor_given(): void
    {
        $constructor = new Constructor('String');
        $definition = new Definition(DefinitionType::data(), 'Bar', 'Person', [$constructor]);

        $this->assertSame('/var/Bar/Person.php', locatePsrPath($this->prefixesPsr4, $this->prefixesPsr0, $definition, $constructor));
    }
}
