<?php

declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use function Fpp\buildAccessors;
use function Fpp\buildMessageName;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;

class BuildMessageNameTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_message_name_from_provided_string(): void
    {
        $constructor = new Constructor('Yeah', [new Argument('id', 'string')]);
        $definition = new Definition('Hell', 'Yeah', [$constructor], [new Deriving\Command()], [], 'tadaa');

        $this->assertSame('tadaa', buildMessageName($definition));
    }

    /**
     * @test
     */
    public function it_builds_message_name_from_class_name(): void
    {
        $constructor = new Constructor('Yeah', [new Argument('id', 'string')]);
        $definition = new Definition('Hell', 'Yeah', [$constructor], [new Deriving\Query()]);

        $this->assertSame('Hell\Yeah', buildMessageName($definition));
    }
}
