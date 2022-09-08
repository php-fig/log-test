# FIG - Log Test
Testing utilities for the [`psr/log`][] package that backs the [PSR-3][] specification.

 * `Psr\Log\Test\LoggerInterfaceTest` provides a base test class for ensuring compliance with the LoggerInterface.
 * `Psr\Log\Test\TestLogger` is a mock class for testing purposes.

## Installation

This package should be used only for tests.

For PHP 8.0+ only, you may remove support for `psr/log: 1.x`:

```json
{
  "require": {
    "php": ">=8.0.0",
    "psr/log": "^2.0 | ^3.0"
  },
  "require-dev": {
    "fig/log-test": "^1.1"
  }
}
```

If the project supports older versions of PHP:

```json
{
  "require": {
    "psr/log": "^1.1.14 | ^2.0"
  },
  "require-dev": {
    "fig/log-test": "^1.0"
  }
}
```

> **Note:** In `psr/log: 3.0.0`, `Psr\Log\LoggerInterface` has union types for method arguments.
> Implementing this interface with PHP 7 compatibility is not possible.

## Versions

The version of `fig/log-test` that is installed after composer dependencies resolution varies with the version of `psr/log`.

| psr/log       | fig/log-test |                                                 |
|---------------|--------------|-------------------------------------------------|
| `^1.1.14`     | `1.0.*`      | Empty package, classes a provided by `psr/log`. |
| `^2.0\|^3.0`  | `^1.1`       | Imports test classes removed from `psr/log`.    |

[`psr/log`]: https://packagist.org/packages/psr/log
[PSR-3]: https://www.php-fig.org/psr/psr-3/
