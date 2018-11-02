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
use function Fpp\Builder\buildMessageName;

class BuildMessageNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_message_name_from_provided_string(): void
    {
        $constructor = new Constructor('Hell\Yeah', [new Argument('id', 'string')]);
        $definition = new Definition(DefinitionType::data(), 'Hell', 'Yeah', [$constructor], [new Deriving\Command()], [], 'tadaa');

        $this->assertSame('tadaa', buildMessageName($definition, $constructor, new DefinitionCollection(), ''));
    }

    /**
     * @test
     */
    public function it_builds_message_name_from_class_name(): void
    {
        $constructor = new Constructor('Hell\Yeah', [new Argument('id', 'string')]);
        $definition = new Definition(DefinitionType::data(), 'Hell', 'Yeah', [$constructor], [new Deriving\Query()]);

        $this->assertSame('Hell\Yeah', buildMessageName($definition, $constructor, new DefinitionCollection(), ''));
    }
}
