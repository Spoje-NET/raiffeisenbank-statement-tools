# Tests Directory

## Overview

This directory contains PHPUnit tests for the Raiffeisenbank Statement Tools project.

## Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/ExampleTest.php

# Run with coverage (requires xdebug)
vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

- Tests should follow PSR-12 coding standards
- Test class names should end with `Test`
- Test methods should start with `test`
- Use meaningful test names that describe what is being tested

## TODO

Replace `ExampleTest.php` with actual test implementations for:
- API client functionality
- Statement downloading
- Statement mailing
- Transaction reporting
- Balance checking
- Error handling and edge cases

## Writing New Tests

When creating new tests:

1. Create a new test file in this directory
2. Extend `PHPUnit\Framework\TestCase`
3. Include proper docblocks
4. Follow PSR-12 standards
5. Use type hints for parameters and return types
6. Test both success and failure scenarios
