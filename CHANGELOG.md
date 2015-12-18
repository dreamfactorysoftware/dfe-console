# Change Log
All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.10][v1.0.10]
## Updated
- Changed version constraint of PHPUNIT in composer.json to ensure only version 4.* is installed. Version 5.* requires PHP 5.6, which is not in the Ubuntu 14.04 repository.

## [1.0.9][v1.0.9]
## Added
- Added `*-dist` copies of supplied branding assets.
## Updated
- Artisan commands **dfe:cluster**, **dfe:server**, and **dfe:mount** now have a "show" operation. 

## [1.0.8][v1.0.8]
## Added
- Migration to correct index on `job_result_t`
## Fixed
- Console registration failure corrected.

## [1.0.6][v1.0.6]
## Added
- **MigrateInstance** Artisan command
- **MigrateInstanceTest** unit test for **MigrateInstance** Artisan command
- Add package `dreamfactory/dfe-capsule` for instance encapsulation
- **Capsule** Artisan command
- **CapsuleTest** unit test for **Capsule** Artisan command
## Updated
- Update in `dreamfactory/dfe-common` for password resets incorporated
- Updated `.env-dist` with new default directories for blueprint and migration services

## [1.0.2][v1.0.2]
### Changed
- **ImportJob** base class incorrect

## [1.0.1][v1.0.1]
### Removed
- Cache files removed from the repository

## [1.0.0][v1.0.0]
### Added
- Initial Release
- Full suite of command line tests

[v1.0.10]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.9...1.0.10
[v1.0.9]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.8...1.0.9
[v1.0.8]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.6...1.0.8
[v1.0.6]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.2...1.0.6
[v1.0.2]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.1...1.0.2
[v1.0.1]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.0...1.0.1
[v1.0.0]: https://github.com/dreamfactorysoftware/dfe-console/compare/master...1.0.0
[unstable]: https://github.com/dreamfactorysoftware/dfe-console/compare/develop...master
