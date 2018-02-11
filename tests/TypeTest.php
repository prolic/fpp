<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /**
     * @test
     */
    public function it_forbids_custom_types(): void
    {
        $this->expectException(\LogicException::class);

        new class extends Type {
        };
    }
}
