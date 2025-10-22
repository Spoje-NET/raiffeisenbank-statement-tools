# WARP.md - Working AI Reference for raiffeisenbank-statement-tools

## Project Overview
**Type**: PHP Project/Debian Package
**Purpose**: Raiffeisenbank Statement Tools
**Status**: Active
**Repository**: git@github.com:Spoje-NET/raiffeisenbank-statement-tools.git

## Key Technologies
- PHP 8.4 or later
- Composer
- Debian Packaging
- PHPUnit (for testing)
- i18n library (for internationalization)

## Architecture & Structure
```
raiffeisenbank-statement-tools/
├── src/                    # Source code (run scripts from here)
├── tests/                  # PHPUnit test files
├── multiflexi/            # MultiFlexi app configurations (*.app.json)
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
└── debian/                # Debian packaging files
```

## Development Workflow

### Prerequisites
- PHP 8.4 or later
- Composer
- Development environment setup

### Setup Instructions
```bash
# Clone the repository
git clone git@github.com:Spoje-NET/raiffeisenbank-statement-tools.git
cd raiffeisenbank-statement-tools

# Install dependencies
composer install
```

### Running the Application
**IMPORTANT**: Always run scripts from the `src/` directory:
```bash
cd src/
php raiffeisenbank-balance.php
```
This ensures relative paths (`../vendor/autoload.php` and `../.env`) work correctly during development. These paths are resolved during Debian packaging via sed commands in the `debian/rules` file.

### Development & Testing
```bash
# After every PHP file edit, lint it immediately (MANDATORY)
php -l path/to/edited/file.php

# Run tests
composer test

# Lint code (check for style issues)
composer lint

# Fix code style issues
composer fix
```

### Build & Package
```bash
dpkg-buildpackage -b -uc
```

## Coding Standards & Best Practices

### Language & Documentation
- **Language**: All code comments, messages, and error messages must be in English
- **Documentation**: Use MarkDown format
- **Commit Messages**: Use imperative mood and keep concise

### PHP Standards
- **Version**: PHP 8.4 or later
- **Coding Standard**: PSR-12
- **Type Hints**: Always include type hints for function parameters and return types
- **Docblocks**: Always include docblocks for functions and classes (purpose, parameters, return types)

### Code Quality
- **Variable Names**: Use meaningful names that describe their purpose
- **Constants**: Avoid magic numbers/strings; define constants instead
- **Exception Handling**: Always handle exceptions properly with meaningful error messages
- **Security**: Ensure code is secure and does not expose sensitive information
- **Performance**: Consider performance and optimize where necessary
- **Maintainability**: Follow best practices and ensure code is maintainable

### Testing
- **Framework**: Use PHPUnit
- **Standard**: Follow PSR-12
- **Coverage**: Always create or update PHPUnit tests when creating/updating classes
- **Unit Tests**: Include unit tests where applicable

### Internationalization
- Use i18n library for internationalization
- Always use `_()` functions for strings that need to be translated

### MultiFlexi Integration
- **App Configurations**: All `multiflexi/*.app.json` files must conform to:
  https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json
- **Reports**: All reports must comply with:
  https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.report.schema.json

## Key Concepts
- **Relative Paths**: The application uses relative paths intentionally during development
- **Path Resolution**: Paths are resolved during Debian packaging via sed commands
- **Working Directory**: Always change to `src/` directory before running scripts
- **Configuration**: Uses `.env` file for environment variables
- **Report Format**: All tools output schema-compliant JSON with status, timestamp, message, artifacts, and metrics
- **Configurable Output**: Report files can be configured via `REPORT_FILE` or `RESULT_FILE` environment variables

## Common Tasks

### Development
1. Edit PHP files
2. **MANDATORY**: Run `php -l filename.php` after every edit
3. Create/update corresponding PHPUnit tests
4. Run test suite
5. Commit with concise imperative commit messages

### Deployment
- Build Debian package with `dpkg-buildpackage -b -uc`
- Deploy to target environment
- Monitor and maintain

## Troubleshooting
- **Lint Errors**: Always run `php -l` on edited files before proceeding
- **Path Issues**: Ensure you're running scripts from `src/` directory
- **Common Issues**: Check logs and error messages
- **Debug Commands**: Use appropriate debugging tools
- **Support**: Check documentation and issue tracker

## Recent Changes

### 2025-10-22
- Fixed critical bugs: unreachable code, exception handling, syntax errors
- Implemented schema-compliant JSON report format across all tools
- Added configurable report output via `REPORT_FILE` environment variable
- Improved balance report structure with proper metrics
- Created PHPUnit test structure with example tests
- Added composer scripts for testing, linting, and code fixing
- Updated MultiFlexi configs: changed `CERT_FILE` type to `file`
- Created CHANGELOG.md to track project changes

## Mandatory Workflow Rules
1. ✅ Always run scripts from `src/` directory
2. ✅ Run `php -l` on every PHP file after editing
3. ✅ Create/update tests when modifying classes
4. ✅ Use English for all code, comments, and messages
5. ✅ Follow PSR-12 coding standard
6. ✅ Include type hints and docblocks
7. ✅ Use `_()` for translatable strings
8. ✅ All reports must follow schema-compliant JSON format
