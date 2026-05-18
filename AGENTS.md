# AGENTS.md

## Scope

Instructions for coding agents working in this repository.

## Project Summary

- VixPHPCS is a PHP_CodeSniffer standard package for PHP 8.4+.
- Production code lives in `src/VixPHPCS`.
- Custom sniffs are grouped by domain in `src/VixPHPCS/Sniffs/<Category>`.
- Tests live in `tests/Sniffs/<Category>` and usually exercise sniffs through inline PHP snippets via `tests/BaseTest.php`.
- The published ruleset is defined in `src/VixPHPCS/ruleset.xml`.
- User-facing rule documentation lives in `docs/SNIFFS.md`, with package overview in `README.md`.

## Working Rules

- Keep changes focused on the requested behavior. Do not refactor unrelated code.
- Do not edit `vendor/` or generated dependency files.
- Preserve the existing PHP style: `declare(strict_types=1);`, PSR-12 formatting, explicit visibility, and strict comparisons.
- Prefer small, deterministic sniff logic. Avoid broad pattern matching that is likely to increase false positives.
- Keep sniff names, rule references, and severities stable unless the task explicitly requires changing them.

## When Adding Or Changing A Sniff

1. Update or add the sniff class under the matching category in `src/VixPHPCS/Sniffs`.
2. Register the rule in `src/VixPHPCS/ruleset.xml` if it is new, or update its configuration if behavior changed.
3. Add or update PHPUnit coverage in the matching `tests/Sniffs/<Category>` file.
4. Update `docs/SNIFFS.md` when rule behavior, examples, severity, or supported patterns change.
5. Update `README.md` when the public rule list or package capabilities change.

## Verification

- For targeted validation, run the relevant PHPUnit test or a focused subset first.
- For PHP code changes, use these repository commands:
  - `composer test`
  - `composer static-analysis`

## Notes For Test Changes

- Follow the existing pattern: each sniff should have a matching test file in the same category.
- Cover both positive and negative cases so warning or error output is intentional.
- Keep test fixtures inline unless an external fixture file is clearly necessary.
