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

The package ships with the `VixPHPCS` ruleset plus additional standalone sniffs you can enable manually. The default ruleset includes best-practice checks such as consistent array key types, alongside formatting, function, PHPDoc, object, and Yii2-focused rules. See [docs/SNIFFS.md](docs/SNIFFS.md) for the full catalog, examples, and configurable parameters.

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
