<?php

declare(strict_types=1);

namespace Fpp;

if (! defined('T_OTHER')) {
    define('T_OTHER', 100000);
}

const parse = '\Fpp\parse';

function parse(string $filename, array $derivingsMap): DefinitionCollection
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

    $nextToken = function () use ($tokens, &$position, &$tokenCount, &$line, $filename): array {
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

    $skipWhitespace = function (array $token) use ($tokens, &$position, $nextToken): array {
        if ($token[0] === T_WHITESPACE) {
            $token = $nextToken();
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

    $nextToken();

    if ($isEndOfFile()) {
        return $collection;
    }

    $token = $nextToken();

    while ($position < $tokenCount) {
        switch ($token[0]) {
            case T_NAMESPACE:
                if ($namespaceFound) {
                    throw ParseError::nestedNamespacesDetected($token[2], $filename);
                }

                $token = $nextToken();
                $requireWhitespace($token);
                $token = $nextToken();
                $requireString($token);
                $namespace = $token[1];
                $token = $nextToken();

                while ($token[0] === T_NS_SEPARATOR) {
                    $token = $nextToken();
                    $requireString($token);
                    $namespace .= '\\' . $token[1];
                    $token = $nextToken();
                }

                $token = $skipWhitespace($token);

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
                $token = $nextToken();
                $requireWhitespace($token);
                $token = $nextToken();
                $requireUcFirstString($token);
                $name = $token[1];
                $token = $nextToken();
                $token = $skipWhitespace($token);
                $messageName = null;

                if ($token[1] !== '=') {
                    throw ParseError::unexpectedTokenFound('=', $token, $filename);
                }

                // parse constructors
                $constructors = [];
                $derivings = [];
                $conditions = [];
                parseConstructor:

                $arguments = [];
                $token = $nextToken();
                $token = $skipWhitespace($token);
                $requireUcFirstString($token);
                $constructorName = $token[1];

                if ($namespace
                    && substr($constructorName, 0, 1) !== '\\'
                    && ! in_array($constructorName, ['String', 'Int', 'Float', 'Bool'], true)
                ) {
                    $constructorName = $namespace . '\\' . $constructorName;
                }

                $token = $nextToken();
                $token = $skipWhitespace($token);

                if ($token[1] === '{') {
                    $arguments = [];
                    parseArguments:

                    while ($token[1] !== '}') {
                        $token = $nextToken();
                        $type = null;
                        $nullable = false;

                        $token = $skipWhitespace($token);

                        if ($token[1] === '?') {
                            $nullable = true;
                            $token = $nextToken();
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

                            if (substr($type, 0, 1) === '\\') {
                                $type = substr($type, 1);
                            }

                            $token = $nextToken();
                            $requireWhitespace($token);
                            $token = $nextToken();
                            $requireVariable($token);
                            $argumentName = substr($token[1], 1);
                            $token = $nextToken();
                            $token = $skipWhitespace($token);

                            if (in_array($token[1], [',', '}'], true)) {
                                $arguments[] = new Argument($argumentName, $type, $nullable);
                                goto parseArguments;
                            }
                        }
                    }

                    $token = $nextToken();
                }

                if ('|' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    $constructors[] = new Constructor($constructorName, $arguments);
                    goto buildDefinition;
                }

                $token = $skipWhitespace($token);
                $constructors[] = new Constructor($constructorName, $arguments);

                if ('|' === $token[1]) {
                    goto parseConstructor;
                }

                if (';' === $token[1]) {
                    goto buildDefinition;
                }

                if ('deriving' === $token[1]) {
                    $token = $nextToken();
                    $token = $skipWhitespace($token);

                    if ($token[1] !== '(') {
                        throw ParseError::unexpectedTokenFound('(', $token, $filename);
                    }

                    $token = $nextToken();

                    while ($token[1] !== ')') {
                        $token = $skipWhitespace($token);
                        $requireString($token);

                        if (! isset($derivingsMap[$token[1]])) {
                            throw ParseError::unknownDeriving($token[2], $filename);
                        }

                        $derivingName = $token[1];
                        $derivings[] = $derivingsMap[$token[1]];
                        $token = $nextToken();
                        $token = $skipWhitespace($token);

                        if (in_array($derivingName, ['AggregateChanged', 'Command', 'DomainEvent', 'Query'], true)
                            && ':' === $token[1]
                        ) {
                            $token = $nextToken();
                            $token = $skipWhitespace($token);

                            if (T_CONSTANT_ENCAPSED_STRING !== $token[0]) {
                                throw ParseError::unexpectedTokenFound('T_CONSTANT_ENCAPSED_STRING', $token, $filename);
                            }

                            $messageName = substr($token[1], 1, -1);

                            $token = $nextToken();
                            $token = $skipWhitespace($token);
                        }

                        if ($token[1] === ',') {
                            $token = $nextToken();
                        }
                    }
                    $token = $nextToken();
                    if (';' === $token[1]) {
                        goto buildDefinition;
                    }
                }

                if ('where' === $token[1]) {
                    $conditionContructor = '_';
                    $token = $nextToken();
                    $token = $skipWhitespace($token);

                    if (T_STRING === $token[0]) {
                        parseConditionsForConstructor:
                        $conditionContructor = $token[1];
                        $token = $nextToken();
                        $token = $skipWhitespace($token);
                        if (':' !== $token[1]) {
                            throw ParseError::unexpectedTokenFound(':', $token, $filename);
                        }

                        $token = $nextToken();
                        $token = $skipWhitespace($token);
                    }

                    if ('|' !== $token[1]) {
                        throw ParseError::unexpectedTokenFound('|', $token, $filename);
                    }

                    $token = $nextToken();
                    $token = $skipWhitespace($token);

                    parseCondition:
                    $bracesOpened = 0;
                    $code = '';

                    while (true) {
                        if (in_array($token[1], ['(', '['], true)) {
                            ++$bracesOpened;
                        }

                        if (in_array($token[1], [')', ']'], true)) {
                            --$bracesOpened;
                        }

                        if (0 === $bracesOpened && T_DOUBLE_ARROW === $token[0]) {
                            break;
                        }

                        $code .= $token[1];
                        $token = $nextToken();
                    }

                    $token = $nextToken();
                    $token = $skipWhitespace($token);

                    if (T_CONSTANT_ENCAPSED_STRING !== $token[0]) {
                        throw ParseError::unexpectedTokenFound('T_CONSTANT_ENCAPSED_STRING', $token, $filename);
                    }

                    $errorMessage = $token[1];

                    $conditions[] = new Condition($conditionContructor, trim($code), substr($errorMessage, 1, -1));

                    $token = $nextToken();
                    $token = $skipWhitespace($token);

                    if ('|' === $token[1]) {
                        $token = $nextToken();
                        $token = $skipWhitespace($token);
                        goto parseCondition;
                    }

                    if (T_STRING === $token[0]) {
                        goto parseConditionsForConstructor;
                    }
                }

                buildDefinition:
                $collection->addDefinition(new Definition($namespace, $name, $constructors, $derivings, $conditions, $messageName));
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
            $token = $nextToken();
        } else {
            ++$position;
        }
    }

    return $collection;
}
