<?php

declare(strict_types=1);

namespace FppTest;

use Fpp\DefinitionCollectionDumper;
use Fpp\Dumper\AggregateChangedDumper;
use Fpp\Dumper\CommandDumper;
use Fpp\Dumper\DataDumper;
use Fpp\Dumper\DomainEventDumper;
use Fpp\Dumper\EnumDumper;
use Fpp\Dumper\QueryDumper;
use Fpp\Parser;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    /**
     * @test
     */
    public function it_parses_dump_and_executes(): void
    {
        $fpp = <<<FPP
namespace Model\Foo;

data Person = {string \$name, ?int \$age}
data Length = {int \$l}
data Red = {}
command RegisterUser : register-user = {string \$name, string \$email}
FPP;

        $parser = new Parser();
        $collection = $parser->parse($fpp);

        $dumper = new DefinitionCollectionDumper([
            'AggregateChanged' => new AggregateChangedDumper(),
            'Data' => new DataDumper(),
            'Enum' => new EnumDumper(),
            'Command' => new CommandDumper(),
            'DomainEvent' => new DomainEventDumper(),
            'Query' => new QueryDumper(),
        ]);
        $code = $dumper->dump($collection);

        echo $code;
        die;
        eval(substr($code, 5));

        $p = \Model\Foo\Person\Person('sasa', 36);

        $this->assertSame('sasa', \Model\Foo\Person\name($p));
        $this->assertSame(36, \Model\Foo\Person\age($p));

        $p2 = \Model\Foo\Person\setAge($p, 37);

        $this->assertNotSame($p, $p2);

        $this->assertSame('sasa', \Model\Foo\Person\name($p2));
        $this->assertSame(37, \Model\Foo\Person\age($p2));

        $length = \Model\Foo\Length\Length(2);

        $this->assertSame(2, \Model\Foo\Length\l($length));

        $red = \Model\Foo\Red\Red();

        $this->assertInstanceOf(\Model\Foo\Red::class, $red);
    }
}
