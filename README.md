# DevStrict - PHP_CodeSniffer Custom Ruleset

[![CI](https://github.com/vix-4800/phpcs-devstrict/workflows/CI/badge.svg)](https://github.com/vix-4800/phpcs-devstrict/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
![PHPStan](https://img.shields.io/badge/style-level%208-brightgreen.svg?&label=phpstan)
![PHP-CS-Fixer](https://img.shields.io/badge/fixer-enabled-brightgreen.svg?&label=php-cs-fixer)
![PHPUnit](https://img.shields.io/badge/tested-enabled-brightgreen.svg?&label=phpunit)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net/)

A comprehensive set of strict PHP_CodeSniffer rules for general PHP, Laravel, and Yii2 projects to maintain high code
quality standards in your projects.

## Installation

### Requirements

- PHP 8.3 or higher
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

    <!-- Use DevStrict rules -->
    <rule ref="DevStrict"/>
</ruleset>
```

## Rulesets

### DevStrict

The main ruleset includes all available rules. See [RULES.md](RULES.md) for detailed documentation of each rule.

**Core Rules:**

- [`DevStrict.Attributes.ForbiddenAttributes`](RULES.md#devstrictattributesforbiddenattributes) - Disallow specific
    attributes (e.g. `#[ArrayShape]`)
- [`DevStrict.ControlStructures.DisallowCountInLoop`](RULES.md#devstrictcontrolstructuresdisallowcountinloop) -
    Prevent `count()` in loop conditions for performance
- [`DevStrict.ControlStructures.DisallowGotoStatement`](RULES.md#devstrictcontrolstructuresdisallowgotostatement) -
    Disallow `goto` statements as anti-pattern
- [`DevStrict.ControlStructures.DisallowThrowInTernary`](RULES.md#devstrictcontrolstructuresdisallowthrowinternary) -
    No exceptions in ternary or null coalescing operators
- [`DevStrict.ControlStructures.UseInArray`](RULES.md#devstrictcontrolstructuresuseinarray) - Suggest `in_array()` for
    multiple OR comparisons
- [`DevStrict.Formatting.MethodChainingIndentation`](RULES.md#devstrictformattingmethodchainingindentation) - Enforce
    four-space indentation for multi-line method chains
- [`DevStrict.Formatting.MethodChainingPerLine`](RULES.md#devstrictformattingmethodchainingperline) - Require one
    chained call per line once the chain is broken
- [`DevStrict.Formatting.ConsistentStatementIndentation`](RULES.md#devstrictformattingconsistentstatementindentation) -
    Keep statements at the same nesting level aligned with identical indentation
- [`DevStrict.Formatting.DisallowMultipleThrowsPerLine`](RULES.md#devstrictformattingdisallowmultiplethrowsperline) -
    Require separate `@throws` annotations for each exception type
- [`DevStrict.Functions.DisallowCastFunctions`](RULES.md#devstrictfunctionsdisallowcastfunctions) - Use type casts
    instead of `strval()`, `intval()`, `floatval()`, `boolval()`
- [`DevStrict.Functions.DisallowHttpFileGetContents`](RULES.md#devstrictfunctionsdisallowhttpfilegetcontents) -
    Disallow `file_get_contents()` for HTTP requests
- [`DevStrict.Functions.PreferModernStringFunctions`](RULES.md#devstrictfunctionsprefermodernstringfunctions) -
    Suggest modern string functions (`str_contains()`, `str_starts_with()`, `str_ends_with()`) instead of `strpos()`
- [`DevStrict.Functions.PreferJsonValidate`](RULES.md#devstrictfunctionspreferjsonvalidate) -
    Suggest `json_validate()` instead of `json_decode()` for validation-only use cases
- [`DevStrict.PhpDoc.DeprecatedTag`](RULES.md#devstrictphpdocdeprecatedtag) - Suggest replacing `@deprecated`
    docblock tag with the `#[\Deprecated]` attribute (PHP 8.4+) for functions, methods, class constants, and enum cases
- [`DevStrict.PhpDoc.DisallowUnusedTemplate`](RULES.md#devstrictphpdocdisallowunusedtemplate) - Disallow unused
    PHPDoc `@template` declarations
- [`DevStrict.PhpDoc.DisallowVoidMixedWithOtherTypes`](RULES.md#devstrictphpdocdisallowvoidmixedwithothertypes) -
    Disallow `void` combined with other types in `@return` tags
- [`DevStrict.Objects.DisallowVariableStaticProperty`](RULES.md#devstrictobjectsdisallowvariablestaticproperty) -
    Forbid `$object::$property` static property access

**Yii2 Framework Rules:**

- [`DevStrict.Yii2.DisallowResponseFormatAssignment`](RULES.md#devstrictyii2disallowresponseformatassignment) - Use
    controller methods like `asJson()` instead of direct assignment
- [`DevStrict.Yii2.PreferActiveRecordShortcuts`](RULES.md#devstrictyii2preferactiverecordshortcuts) - Suggest
    `findOne()`/`findAll()` over `find()->where()->one()/all()`
- [`DevStrict.Yii2.PreferExistsOverCount`](RULES.md#devstrictyii2preferexistsovercount) - Use `exists()` instead of
    `count() > 0` for better performance
- [`DevStrict.Yii2.PreferIsGuestOverUserIdCheck`](RULES.md#devstrictyii2preferisguestoveruseridcheck) - Use
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
