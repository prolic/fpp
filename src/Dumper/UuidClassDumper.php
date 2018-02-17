<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class UuidClassDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $code = '';
        $indent = '';

        if ($definition->namespace() !== '') {
            $code = "namespace {$definition->namespace()} {\n    ";
            $indent = '    ';
        }

        $variableName = lcfirst($definition->name());
        $code .= "\n" . $indent . "use Ramsey\Uuid\Uuid;\n\n$indent";
        $code .= <<<CODE
final class {$definition->name()}
$indent{
$indent    private \$uuid;

$indent    public static function generate(): {$definition->name()}
$indent    {
$indent        return new self(Uuid::uuid4());
$indent    }

$indent    public static function fromString(string \$$variableName): {$definition->name()}
$indent    {
$indent        return new self(Uuid::fromString(\$$variableName));
$indent    }

$indent    private function __construct(Uuid \$$variableName)
$indent    {
$indent        \$this->uuid = \$$variableName;
$indent    }

$indent    public function toString(): string
$indent    {
$indent        return \$this->uuid->toString();
$indent    }

$indent    public function __toString(): string
$indent    {
$indent        return \$this->uuid->toString();
$indent    }

$indent    public function equals({$definition->name()} \$$variableName): bool
$indent    {
$indent        return \$this->uuid->equals(\${$variableName}->uuid);
$indent    }
$indent}
CODE;
        if ($definition->namespace() !== '') {
            $code .= "\n}";
        }

        $code .= "\n\n";

        return $code;
    }
}
