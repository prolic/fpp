<?php

declare(strict_types=1);

namespace Fpp;

if (! defined('T_OTHER')) {
    define('T_OTHER', 100000);
}

const parse = '\Fpp\parse';

function parse(string $filename): DefinitionCollection
{
    if (! is_file($filename)) {
        throw new \RuntimeException("'$filename' is not a file");
    }

    if (! is_readable($filename)) {
        throw new \RuntimeException("'$filename' is not readable");
    }

    $namespaceFound = false;
    $contents = file_get_contents($filename);
    $tokens = token_get_all("<?php\n\n$contents");

    $collection = new DefinitionCollection();

    $tokenCount = count($tokens);
    $position = 0;
    $line = 1;
    $namespace = '';

    $nextToken = function (array $tokens) use (&$position, &$tokenCount, &$line, $filename): array {
        if ($position === $tokenCount - 1) {
            throw ParseError::unexpectedEndOfFile($filename);
        }

        $token = $tokens[++$position];

        if (! is_array($token)) {
            $token = [
                T_OTHER,
                $token,
                $line,
            ];
        } else {
            $token[2] = $token[2] - 1;
            $line = $token[2];
        }

        return $token;
    };

    $skipWhitespace = function (array $token, array $tokens) use (&$position, $nextToken): array {
        if ($token[0] === T_WHITESPACE) {
            $token = $nextToken($tokens);
        }

        return $token;
    };

    $requireWhitespace = function (array $token) use ($filename): void {
        if ($token[0] !== T_WHITESPACE) {
            throw ParseError::unexpectedTokenFound(' ', $token, $filename);
        }
    };

    $requireString = function (array $token) use ($filename): void {
        if ($token[0] !== T_STRING) {
            throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
        }
    };

    $requireVariable = function (array $token) use ($filename): void {
        if ($token[0] !== T_VARIABLE) {
            throw ParseError::unexpectedTokenFound('T_VARIABLE', $token, $filename);
        }
    };

    $requireUcFirstString = function (array $token) use ($filename): void {
        if ($token[0] !== T_STRING) {
            throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
        }

        if ($token[1][0] === strtolower($token[1][0])) {
            throw ParseError::lowerCaseDefinitionName($token, $filename);
        }
    };

    $isEndOfFile = function () use (&$position, &$tokenCount): bool {
        return $position === ($tokenCount - 1);
    };

    $nextToken($tokens);

    if ($isEndOfFile()) {
        return $collection;
    }

    $token = $nextToken($tokens);

    while ($position < $tokenCount) {
        switch ($token[0]) {
            case T_NAMESPACE:
                if ($namespaceFound) {
                    throw ParseError::nestedNamespacesDetected($token[2], $filename);
                }

                $token = $nextToken($tokens);
                $requireWhitespace($token);
                $token = $nextToken($tokens);
                $requireString($token);
                $namespace = $token[1];
                $token = $nextToken($tokens);

                while ($token[0] === T_NS_SEPARATOR) {
                    $token = $nextToken($tokens);
                    $requireString($token);
                    $namespace .= '\\' . $token[1];
                    $token = $nextToken($tokens);
                }

                $token = $skipWhitespace($token, $tokens);

                if ($token[1] === '{') {
                    $namespaceFound = true;
                    break;
                }

                if ($token[1] !== ';') {
                    throw ParseError::unexpectedTokenFound(';', $token, $filename);
                }
                break;
            case T_STRING:
                if ($token[1] !== 'data') {
                    throw ParseError::unknownDefinition($token, $filename);
                }

                // parse name (incl. message name for prooph messages)
                $token = $nextToken($tokens);
                $requireWhitespace($token);
                $token = $nextToken($tokens);
                $requireUcFirstString($token);
                $name = $token[1];
                $token = $nextToken($tokens);
                $token = $skipWhitespace($token, $tokens);
                $messageName = null;

                if ($token[1] !== '=') {
                    throw ParseError::unexpectedTokenFound('=', $token, $filename);
                }

                // parse constructors
                $constructors = [];
                parseConstructor:

                $arguments = [];
                $token = $nextToken($tokens);
                $token = $skipWhitespace($token, $tokens);
                $requireUcFirstString($token);
                $constructorName = $token[1];
                $token = $nextToken($tokens);
                $token = $skipWhitespace($token, $tokens);

                if ($token[1] === '{') {
                    $arguments = [];
                    parseArguments:

                    while ($token[1] !== '}') {
                        $token = $nextToken($tokens);
                        $type = null;
                        $nullable = false;

                        $token = $skipWhitespace($token, $tokens);

                        if ($token[1] === '?') {
                            $nullable = true;
                            $token = $nextToken($tokens);
                            $requireString($token);
                        }

                        if ($token[0] === T_STRING) {
                            $type = $token[1];

                            if (! in_array($type, ['string', 'int', 'bool', 'float'])) {
                                $requireUcFirstString($token);

                                if (substr($type, 0, 1) !== '\\') {
                                    $type = $namespace . '\\' . $type;
                                }
                            }

                            $token = $nextToken($tokens);
                            $requireWhitespace($token);
                            $token = $nextToken($tokens);
                            $requireVariable($token);
                            $argumentName = substr($token[1], 1);
                            $token = $nextToken($tokens);
                            $token = $skipWhitespace($token, $tokens);

                            if (in_array($token[1], [',', '}'], true)) {
                                $arguments[] = new Argument($argumentName, $type, $nullable);
                                goto parseArguments;
                            }
                        }
                    }
                }

                if ('|' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto buildDefinition;
                }

                if (! $isEndOfFile()) {
                    $token = $nextToken($tokens);
                }

                $token = $skipWhitespace($token, $tokens);

                if ('|' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto buildDefinition;
                }

                buildDefinition:
                $collection->addDefinition(new Definition($namespace, $name, $constructors, [], [], $messageName));
                break;
            case T_WHITESPACE:
                break;
            case T_OTHER:
                if ($token[1] === '}' && $namespaceFound) {
                    $namespaceFound = false;
                    $namespace = '';
                    break;
                }
                throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
                break;
            default:
                throw ParseError::unexpectedTokenFound('T_STRING', $token, $filename);
        }

        if ($position < $tokenCount - 1) {
            $token = $nextToken($tokens);
        } else {
            ++$position;
        }
    }

    return $collection;
}
