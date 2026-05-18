# VixPHPCS - PHP_CodeSniffer Custom Ruleset

[![PHPStan](https://github.com/vix-4800/phpcs-sniffs/actions/workflows/phpstan.yml/badge.svg)](https://github.com/vix-4800/phpcs-sniffs/actions/workflows/phpstan.yml)
[![Tests](https://github.com/vix-4800/phpcs-sniffs/actions/workflows/tests.yml/badge.svg)](https://github.com/vix-4800/phpcs-sniffs/actions/workflows/tests.yml)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A comprehensive set of strict PHP_CodeSniffer rules for general PHP, Laravel, and Yii2 projects to maintain high code
quality standards in your projects.

## Installation

### Requirements

- PHP 8.4 or higher
- Composer

### Install via Composer

```bash
composer require --dev vix/phpcs-sniffs
```

## Usage

### Basic Configuration

Create a `phpcs.xml` file in your project root:

```xml
<?xml version="1.0"?>
<ruleset name="MyProject">
    <description>My project coding standard</description>

    <!-- Paths to check -->
    <file>src</file>
    <file>tests</file>

    <!-- Use VixPHPCS rules -->
    <rule ref="VixPHPCS"/>
</ruleset>
```

## Rulesets

### VixPHPCS

The main ruleset includes all available rules. See [RULES.md](RULES.md) for detailed documentation of each rule.

**Core Rules:**

- [`VixPHPCS.Attributes.ForbiddenAttributes`](RULES.md#vixphpcsattributesforbiddenattributes) - Disallow specific
    attributes (e.g. `#[ArrayShape]`)
- [`VixPHPCS.ControlStructures.DisallowCountInLoop`](RULES.md#vixphpcscontrolstructuresdisallowcountinloop) -
    Prevent `count()` in loop conditions for performance
- [`VixPHPCS.ControlStructures.DisallowGotoStatement`](RULES.md#vixphpcscontrolstructuresdisallowgotostatement) -
    Disallow `goto` statements as anti-pattern
- [`VixPHPCS.ControlStructures.DisallowThrowInTernary`](RULES.md#vixphpcscontrolstructuresdisallowthrowinternary) -
    No exceptions in ternary or null coalescing operators
- [`VixPHPCS.ControlStructures.UseInArray`](RULES.md#vixphpcscontrolstructuresuseinarray) - Suggest `in_array()` for
    multiple OR comparisons
- [`VixPHPCS.Formatting.MethodChainingIndentation`](RULES.md#vixphpcsformattingmethodchainingindentation) - Enforce
    four-space indentation for multi-line method chains
- [`VixPHPCS.Formatting.MethodChainingPerLine`](RULES.md#vixphpcsformattingmethodchainingperline) - Require one
    chained call per line once the chain is broken
- [`VixPHPCS.Formatting.ConsistentStatementIndentation`](RULES.md#vixphpcsformattingconsistentstatementindentation) -
    Keep statements at the same nesting level aligned with identical indentation
- [`VixPHPCS.Formatting.DisallowMultipleThrowsPerLine`](RULES.md#vixphpcsformattingdisallowmultiplethrowsperline) -
    Require separate `@throws` annotations for each exception type
- [`VixPHPCS.Functions.DisallowCastFunctions`](RULES.md#vixphpcsfunctionsdisallowcastfunctions) - Use type casts
    instead of `strval()`, `intval()`, `floatval()`, `boolval()`
- [`VixPHPCS.Functions.DisallowHttpFileGetContents`](RULES.md#vixphpcsfunctionsdisallowhttpfilegetcontents) -
    Disallow `file_get_contents()` for HTTP requests
- [`VixPHPCS.Functions.PreferModernStringFunctions`](RULES.md#vixphpcsfunctionsprefermodernstringfunctions) -
    Suggest modern string functions (`str_contains()`, `str_starts_with()`, `str_ends_with()`) instead of `strpos()`
- [`VixPHPCS.Functions.PreferJsonValidate`](RULES.md#vixphpcsfunctionspreferjsonvalidate) -
    Suggest `json_validate()` instead of `json_decode()` for validation-only use cases
- [`VixPHPCS.PhpDoc.DeprecatedTag`](RULES.md#vixphpcsphpdocdeprecatedtag) - Suggest replacing `@deprecated`
    docblock tag with the `#[\Deprecated]` attribute (PHP 8.4+) for functions, methods, class constants, and enum cases
- [`VixPHPCS.PhpDoc.DisallowUnusedTemplate`](RULES.md#vixphpcsphpdocdisallowunusedtemplate) - Disallow unused
    PHPDoc `@template` declarations
- [`VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes`](RULES.md#vixphpcsphpdocdisallowvoidmixedwithothertypes) -
    Disallow `void` combined with other types in `@return` tags
- [`VixPHPCS.Objects.DisallowVariableStaticProperty`](RULES.md#vixphpcsobjectsdisallowvariablestaticproperty) -
    Forbid `$object::$property` static property access

**Yii2 Framework Rules:**

- [`VixPHPCS.Yii2.DisallowResponseFormatAssignment`](RULES.md#vixphpcsyii2disallowresponseformatassignment) - Use
    controller methods like `asJson()` instead of direct assignment
- [`VixPHPCS.Yii2.PreferActiveRecordShortcuts`](RULES.md#vixphpcsyii2preferactiverecordshortcuts) - Suggest
    `findOne()`/`findAll()` over `find()->where()->one()/all()`
- [`VixPHPCS.Yii2.PreferExistsOverCount`](RULES.md#vixphpcsyii2preferexistsovercount) - Use `exists()` instead of
    `count() > 0` for better performance
- [`VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck`](RULES.md#vixphpcsyii2preferisguestoveruseridcheck) - Use
    `Yii::$app->user->isGuest` instead of checking `id` directly

## Development

### Guidelines

- All new sniffs must have tests
- Follow the existing code style
- Update documentation when necessary
- Ensure all checks pass (`composer check`)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Additional Resources

- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [Creating Custom Sniffs](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial)
