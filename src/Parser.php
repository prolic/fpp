<?php

declare(strict_types=1);

namespace Fpp;

if (! defined('T_OTHER')) {
    define('T_OTHER', 100000);
}

final class Parser
{
    /**
     * @var int
     */
    private $tokenCount;

    /**
     * @var int
     */
    private $line = 0;

    /**
     * @var bool
     */
    private $namespaceFound;

    /**
     * @var string
     */
    private $filename = '';

    public function parseFile(string $filename): DefinitionCollection
    {
        $this->namespaceFound = false;
        $this->filename = $filename;

        $collection = $this->parse(file_get_contents($filename));

        $this->filename = '';

        return $collection;
    }

    public function parse(string $contents): DefinitionCollection
    {
        $this->namespaceFound = false;
        $collection = new DefinitionCollection();

        $tokens = token_get_all("<?php\n\n$contents");
        $this->tokenCount = count($tokens);
        $position = 0;
        $namespace = '';

        $token = $this->nextToken($tokens, $position);

        while ($position < $this->tokenCount - 1) {
            switch ($token[0]) {
                case T_OPEN_TAG:
                    break;
                case T_NAMESPACE:
                    if ($this->namespaceFound) {
                        throw ParseError::nestedNamespacesDetected($token[2], $this->filename);
                    }
                    $namespace = $this->parseNamespace($tokens, $position);
                    break;
                case T_STRING:
                    $typeString = ucfirst($token[1]);
                    switch ($typeString) {
                        case Type\Data::VALUE:
                            list($name) = $this->parseName($tokens, $position);
                            list($arguments) = $this->parseArguments($tokens, $position);
                            list($derivings, $token) = $this->parseDerivings($tokens, $position, true);
                            $collection->addDefinition(new Definition(new Type\Data(), $namespace, $name, $arguments, $derivings));

                            if ($token[0] === T_STRING) {
                                // next definition found
                                continue 3;
                            }
                            break;
                        case Type\Enum::VALUE:
                            list($name) = $this->parseName($tokens, $position);
                            list($arguments, $token) = $this->parseEnumTypes($tokens, $position);
                            $collection->addDefinition(new Definition(new Type\Enum(), $namespace, $name, $arguments));

                            if ($token[0] === T_STRING) {
                                // next definition found
                                continue 3;
                            }
                            break;
                        case Type\AggregateChanged::VALUE:
                        case Type\Command::VALUE:
                        case Type\DomainEvent::VALUE:
                        case Type\Query::VALUE:
                            list($name, $messageName) = $this->parseNameWithMessage($tokens, $position);
                            list($arguments) = $this->parseArguments($tokens, $position);
                            list($derivings, $token) = $this->parseDerivings($tokens, $position, false);
                            $typeClass = __NAMESPACE__ . '\\Type\\' . $typeString;
                            $collection->addDefinition(new Definition(new $typeClass, $namespace, $name, $arguments, $derivings, $messageName));

                            if ($token[0] === T_STRING) {
                                // next definition found
                                continue 3;
                            }
                            break;
                        case Type\Uuid::VALUE:
                            list($name) = $this->parseName($tokens, $position, false);
                            $collection->addDefinition(new Definition(new Type\Uuid(), $namespace, $name));
                            break;
                    }
                    break;
                case T_WHITESPACE:
                    break;
                case T_OTHER:
                    if ($token[1] === '}') {
                        if ($this->namespaceFound) {
                            $this->namespaceFound = false;
                            $namespace = '';
                        } else {
                            throw ParseError::unexpectedTokenFound('T_STRING or T_WHITESPACE', $token, $this->filename);
                        }
                    }
                    break;
                default:
                    throw ParseError::unexpectedTokenFound('T_STRING or T_WHITESPACE', $token, $this->filename);
            }

            if ($position + 1 < $this->tokenCount) {
                $token = $this->nextToken($tokens, $position);
            }
        }

        return $collection;
    }

    private function nextToken(array $tokens, int &$position): array
    {
        if ($position === $this->tokenCount - 1) {
            throw ParseError::unexpectedEndOfFile($this->filename);
        }

        $token = $tokens[++$position];

        if (! is_array($token)) {
            $token = [
                T_OTHER,
                $token,
                $this->line,
            ];
        } else {
            $token[2] = $token[2] - 2;
            $this->line = $token[2];
        }

        return $token;
    }

    private function parseNamespace(array $tokens, int &$position): string
    {
        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::unexpectedTokenFound(' ', $token, $this->filename);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token, $this->filename);
        }

        $namespace = $token[1];

        $token = $this->nextToken($tokens, $position);

        while ($token[0] === T_NS_SEPARATOR) {
            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token, $this->filename);
            }

            $namespace .= '\\' . $token[1];

            $token = $this->nextToken($tokens, $position);
        }

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] === '{') {
            $this->namespaceFound = true;

            return $namespace;
        }

        if ($token[1] !== ';') {
            throw ParseError::unexpectedTokenFound(';', $token, $this->filename);
        }

        return $namespace;
    }

    private function parseName(array $tokens, int &$position, bool $withAssignment = true): array
    {
        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::unexpectedTokenFound(' ', $token, $this->filename);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token, $this->filename);
        }

        $name = $token[1];

        if (! $withAssignment) {
            return [$name, $token];
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] !== '=') {
            throw ParseError::unexpectedTokenFound('=', $token, $this->filename);
        }

        return [$name, $token];
    }

    private function parseNameWithMessage(array $tokens, int &$position): array
    {
        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::unexpectedTokenFound(' ', $token, $this->filename);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token, $this->filename);
        }

        $name = $token[1];

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        $messageName = null;

        if ($token[1] === ':') {
            $token = $this->nextToken($tokens, $position);

            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::unexpectedTokenFound('T_STRING', $token, $this->filename);
            }

            $messageName = $token[1];

            $token = $this->nextToken($tokens, $position);

            while ($token[0] !== T_WHITESPACE
                && $token[1] !== '='
            ) {
                $messageName .= $token[1];

                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }
        }

        if ($token[1] !== '=') {
            throw ParseError::unexpectedTokenFound('=', $token, $this->filename);
        }

        return [$name, $messageName, $token];
    }

    private function parseArguments(array $tokens, int &$position): array
    {
        $arguments = [];

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] !== '{') {
            throw ParseError::unexpectedTokenFound('{', $token, $this->filename);
        }

        $token = $this->nextToken($tokens, $position);

        while ($token[1] !== '}') {
            $typeHint = null;

            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[1] === '?') {
                $typeHint = '?';
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token, $this->filename);
            }

            $typeHint .= $token[1];
            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_WHITESPACE) {
                throw ParseError::unexpectedTokenFound(' ', $token, $this->filename);
            }

            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_VARIABLE) {
                throw ParseError::unexpectedTokenFound('T_VARIABLE', $token, $this->filename);
            }

            $name = substr($token[1], 1);
            $arguments[] = new Argument($name, $typeHint, null);

            $token = $this->nextToken($tokens, $position);

            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[1] === ',') {
                $token = $this->nextToken($tokens, $position);
            }
        }

        return [$arguments, $token];
    }

    private function parseEnumTypes(array $tokens, int &$position): array
    {
        $arguments = [];

        $token = $this->nextToken($tokens, $position);

        while (true) {
            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token, $this->filename);
            }

            $name = $token[1];

            $arguments[] = new Argument($name, null, null);

            if ($position === $this->tokenCount - 1) {
                break;
            }

            $token = $this->nextToken($tokens, $position);

            if ($token[0] === T_WHITESPACE) {
                if ($position === $this->tokenCount - 1) {
                    break;
                }
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[1] === '|') {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] === T_WHITESPACE) {
                if ($position === $this->tokenCount - 1) {
                    break;
                }
                $token = $this->nextToken($tokens, $position);
            }

            if (in_array(ucfirst($token[1]), Type::OPTION_VALUES)) {
                break;
            }
        }

        return [$arguments, $token];
    }

    private function parseDerivings(array $tokens, int &$position, bool $allow): array
    {
        $derivings = [];

        if (($this->tokenCount - 1) === $position) {
            return [$derivings, null];
        }

        $token = $this->nextToken($tokens, $position);

        if (($this->tokenCount - 1) === $position) {
            return [$derivings, null];
        }

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if (! $allow && $token[1] === 'deriving') {
            throw ParseError::unknownDeriving($token[2], $this->filename);
        }

        if ($token[1] !== 'deriving') {
            return [$derivings, $token];
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] !== '(') {
            throw ParseError::unexpectedTokenFound('(', $token, $this->filename);
        }

        $token = $this->nextToken($tokens, $position);

        while ($token[1] !== ')') {
            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token, $this->filename);
            }

            if (! in_array($token[1], Deriving::OPTION_VALUES, true)) {
                throw ParseError::unknownDeriving($token[2], $this->filename);
            }

            $fqcn = __NAMESPACE__ . '\\Deriving\\' . $token[1];
            $derivings[] = new $fqcn();

            $token = $this->nextToken($tokens, $position);

            if ($token[0] === T_WHITESPACE) {
                throw ParseError::unexpectedTokenFound(' ', $token, $this->filename);
            }

            if ($token[1] === ',') {
                $token = $this->nextToken($tokens, $position);
            }
        }

        return [$derivings, $token];
    }
}
