# Catalyst Unit Test Harness

## Purpose

This directory contains short PHP tests for pure framework behavior: helpers,
validators, services, config normalization, internal contracts and regressions
that do not need a browser.

## Runner

Catalyst currently does not depend on PHPUnit or Pest. Use the local runner:

```powershell
php test\framework\UnitTest\run.php
```

Test files must end with `Test.php` and return no output on success unless the
test intentionally reports diagnostic data. Throw an exception or use
`CatalystTest\Support\Assert` for failures.

## Rules

- Keep tests independent and short.
- Do not open a browser.
- Do not rely on WSDD, Docker, MFA, network services or local OS applications.
- Do not use SQLite or in-memory database harnesses for framework database
  behavior. Put MySQL/MariaDB-backed coverage in `test/framework/IntegrationTest`.
- Put environment-backed behavior in CLI smokes or Playwright tests instead.
- Do not place passwords or other credentials in test source files.
