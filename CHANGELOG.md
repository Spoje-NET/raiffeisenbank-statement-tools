# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- PHPUnit test structure with `phpunit.xml` configuration
- Basic test examples in `tests/ExampleTest.php`
- Test documentation in `tests/README.md`
- Composer scripts for testing, linting, and code fixing
- Schema-compliant JSON report format for all tools (complies with MultiFlexi report schema)
- Configurable report output via `REPORT_FILE` environment variable
- Structured metrics in balance reports with currency folder details
- Balance metrics now include individual currency balances (e.g., `czk_clab`, `eur_clbd`)

### Changed
- All tools now output consistent schema-compliant JSON reports per MultiFlexi schema
- Balance report structure improved with proper array access and structured metrics
- Report output strategy unified across all scripts (configurable via `REPORT_FILE`)
- MultiFlexi config: `CERT_FILE` type changed from `string` to `file` for better file handling
- Updated README with development section, testing instructions, and new features
- Report `artifacts` now contain arrays of file paths/URLs as per schema requirements
- Balance and transaction metrics flattened to use only scalar values (strings/numbers)

### Fixed
- Fixed unreachable code in `raiffeisenbank-transaction-report.php` (report generation after exit)
- Fixed exception handling in `raiffeisenbank-statement-mailer.php` (proper error reporting)
- Fixed missing closing brace in `raiffeisenbank-statement-mailer.php`
- Fixed balance retrieval array access bug (incorrect metrics structure)
- Fixed report artifacts to comply with MultiFlexi report schema (arrays of strings only)
- Fixed metrics to contain only scalar values (no nested arrays/objects)
- All PHP files now pass syntax validation
- All MultiFlexi `*.app.json` files validated for correct structure

### Security
- `.env` file properly excluded from version control via `.gitignore`

## Previous Versions

See Git history for changes prior to this changelog.
