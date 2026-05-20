<?php

declare(strict_types=1);

namespace PHP_CodeSniffer\Files;

/**
 * PHPStan type stub for PHP_CodeSniffer\Files\File.
 *
 * Provides a more precise return type for getTokens() so that individual token
 * key accesses are not typed as mixed at PHPStan level max.
 */
class File
{
    /**
     * @return array<int, array{
     *     code: int|string,
     *     content: string,
     *     type: string,
     *     line: int,
     *     column: int,
     *     level: int,
     *     conditions: array<int, int|string>,
     *     nested_parenthesis?: array<int, int>,
     *     scope_opener?: int,
     *     scope_closer?: int,
     *     scope_condition?: int,
     *     parenthesis_opener?: int,
     *     parenthesis_closer?: int,
     *     parenthesis_owner?: int,
     *     bracket_opener?: int,
     *     bracket_closer?: int,
     *     attribute_closer?: int,
     *     comment_opener?: int,
     *     comment_closer?: int,
     * }>
     */
    public function getTokens(): array
    {
    }
}
