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
    private $namespaceFound = false;

    public function parse(string $contents): DefinitionCollection
    {
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
                        throw ParseError::nestedNamespacesDetected($token[2]);
                    }
                    $namespace = $this->parseNamespace($tokens, $position);
                    break;
                case T_STRING:
                    switch ($token[1]) {
                        case 'data':
                            list($name, $token) = $this->parseName($tokens, $position);
                            list($arguments, $token) = $this->parseArguments($tokens, $position);
                            list($derivings, $token) = $this->parseDerivings($tokens, $position, true);
                            $collection->addDefinition(new Definition(Type::DATA(), $namespace, $name, $arguments, $derivings));

                            if ($token[0] === T_STRING) {
                                // next definition found
                                continue 3;
                            }
                            break;
                        case 'command':
                        case 'event':
                        case 'query':
                            $type = Type::get($token[1]);
                            list($name, $messageName, $token) = $this->parseNameWithMessage($tokens, $position);
                            list($arguments, $token) = $this->parseArguments($tokens, $position);
                            list($derivings, $token) = $this->parseDerivings($tokens, $position, false);
                            $collection->addDefinition(new Definition($type, $namespace, $name, $arguments, $derivings, $messageName));

                            if ($token[0] === T_STRING) {
                                // next definition found
                                continue 3;
                            }
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
                            throw ParseError::invalidToken($token[2]);
                        }
                    }
                    break;
                default:
                    throw ParseError::invalidToken($token[2]);
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
            throw ParseError::unexpectedEndOfFile();
        }

        $token = $tokens[++$position];

        if (! is_array($token)) {
            $token = [
                T_OTHER,
                $token,
                $this->line
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
            throw ParseError::expectedWhiteSpace($token[2]);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token[2]);
        }

        $namespace = $token[1];

        $token = $this->nextToken($tokens, $position);

        while ($token[0] === T_NS_SEPARATOR) {
            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token[2]);
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
            throw ParseError::invalidToken($token[2]);
        }

        return $namespace;
    }

    private function parseName(array $tokens, int &$position): array
    {
        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::expectedWhiteSpace($token[2]);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token[2]);
        }

        $name = $token[1];

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] !== '=') {
            throw ParseError::expectedEquals($token[2]);
        }

        return [$name, $token];
    }

    private function parseNameWithMessage(array $tokens, int &$position): array
    {
        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::expectedWhiteSpace($token[2]);
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] !== T_STRING) {
            throw ParseError::expectedString($token[2]);
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
                throw ParseError::invalidToken($token[2]);
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
            throw ParseError::expectedEquals($token[2]);
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
            throw ParseError::expectedCurlyBraces($token[2]);
        }

        $token = $this->nextToken($tokens, $position);

        while ($token[1] !== '}') {
            $typehint = null;

            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[1] === '?') {
                $typehint = '?';
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token[2]);
            }

            $typehint .= $token[1];
            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_WHITESPACE) {
                throw ParseError::expectedWhiteSpace($token[2]);
            }

            $token = $this->nextToken($tokens, $position);

            if ($token[0] !== T_VARIABLE) {
                throw ParseError::expectedVariable($token[2]);
            }

            $name = substr($token[1], 1);
            $arguments[] = new Argument($name, $typehint);

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

    private function parseDerivings(array $tokens, int &$position, bool $allow): array
    {
        $derivings = new DerivingSet();

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
            throw ParseError::unknownDeriving($token[2]);
        }

        if ($token[1] !== 'deriving') {
            return [$derivings, $token];
        }

        $token = $this->nextToken($tokens, $position);

        if ($token[0] === T_WHITESPACE) {
            $token = $this->nextToken($tokens, $position);
        }

        if ($token[1] !== '(') {
            throw ParseError::expectedRoundBraces($token[2]);
        }

        $token = $this->nextToken($tokens, $position);

        while ($token[1] !== ')') {
            if ($token[0] === T_WHITESPACE) {
                $token = $this->nextToken($tokens, $position);
            }

            if ($token[0] !== T_STRING) {
                throw ParseError::expectedString($token[2]);
            }

            if (! Deriving::has($token[1])) {
                throw ParseError::unknownDeriving($token[2]);
            }

            $derivings->attach(Deriving::get($token[1]));

            $token = $this->nextToken($tokens, $position);

            if ($token[0] === T_WHITESPACE) {
                throw ParseError::expectedWhiteSpace($token[2]);
            }

            if ($token[1] === ',') {
                $token = $this->nextToken($tokens, $position);
            }
        }

        return [$derivings, $token];
    }
}
