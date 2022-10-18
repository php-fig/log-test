# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.1.0] - 2022-10-18
### Added
- Import classes from [`psr/log`][] `v1.1.4`, for compatibility with `v2.0.0` and `v3.0.0`.

## [1.0.0] - 2022-09-07
### Changed
- Compatible with PHP 7.4 and 8.x. Dropped support for lower versions as Test class is marked @requires PHP 7.4
- Initial release. This ports the test for and from [`psr/log` v1.1][], according to
[decision][1].
[`psr/log`]: https://packagist.org/packages/psr/log
[1]: https://github.com/php-fig/log/pull/76#issuecomment-858743302

## [0.0.0] - 2022-08-15
### Changed
- Class namespaces are now under the Fig\\ namespace.
- Unreleased. Transitioned from the psr/log-util package to the fig/log-test package due to policy discussion.
