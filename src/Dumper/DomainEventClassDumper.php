<?php

declare(strict_types=1);

namespace Fpp\Dumper;

use Fpp\Definition;

class DomainEventClassDumper implements Dumper
{
    public function dump(Definition $definition): string
    {
        $code = '';
        $indent = '';

        $messageName = $definition->messageName();

        if (null === $messageName) {
            $messageName = '\\' . $definition->name();
        }

        if ($definition->namespace() !== '') {
            $code = "namespace {$definition->namespace()} {\n    ";
            $indent = '    ';

            if (null === $definition->messageName()) {
                $messageName = '\\' . $definition->namespace() . $messageName;
            }
        }

        $code .= <<<CODE
final class {$definition->name()} extends \Prooph\Common\Messaging\DomainEvent implements \Prooph\Common\Messaging\PayloadConstructable
$indent{\n$indent    use \Prooph\Common\Messaging\PayloadTrait;

$indent    protected \$messageName = '$messageName';

CODE;

        foreach ($definition->arguments() as $argument) {
            $code .= "$indent    private \${$argument->name()};\n";
        }

        $code .= "\n$indent    public function __construct(";

        foreach ($definition->arguments() as $argument) {
            if ($argument->nullable()) {
                $code .= '?';
            }
            if ($argument->namespace() && substr($argument->namespace(), 0, 1) !== '\\') {
                $ns = '\\' . $definition->namespace() . '\\' . $argument->namespace();
            } else {
                $ns = $argument->namespace();
            }
            $code .= "$ns{$argument->typeHint()} \${$argument->name()}, ";
        }

        if (! empty($definition->arguments())) {
            $code = substr($code, 0, -2);
        }

        $code .= ")\n$indent    {\n";

        $code .= "$indent        parent::__construct([\n";
        foreach ($definition->arguments() as $argument) {
            $code .= "$indent            '{$argument->name()}' => \${$argument->name()},\n";
        }
        $code .= "$indent        ]);\n";

        foreach ($definition->arguments() as $argument) {
            $code .= "$indent        \$this->{$argument->name()} = \${$argument->name()};\n";
        }

        $code .= "$indent    }\n\n";

        foreach ($definition->arguments() as $argument) {
            $returnType = '';
            if ($argument->typeHint()) {
                if ($argument->nullable()) {
                    $returnType = '?';
                }
                $returnType = ': ' . $returnType . $argument->typeHint();
            }

            $code .= <<<CODE
$indent    public function {$argument->name()}()$returnType
$indent    {
$indent        if (! isset(\$this->{$argument->name()})) {
$indent            \$this->{$argument->name()} = \$this->payload['{$argument->name()}'];
$indent        }

$indent        return \$this->{$argument->name()};
$indent    }


CODE;
        }

        $code = substr($code, 0, -1);
        $code .= "$indent}";

        if ($definition->namespace() !== '') {
            $code .= "\n}";
        }

        $code .= "\n\n";

        return $code;
    }
}
