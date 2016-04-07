# Change Log
All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.27][v1.0.27]
### Added
- New `dfe:move-instance` command to relocate instances from one server to another
- New `DFE_REMOTE_*` environment variables added to `.env-dist` 
- New `dfe:daily` command added for performing daily maintenance tasks
- New `config/tasks.php` configuration file added for configuration of the new `dfe:daily` command
- New non-activated instance deprovisioning automation introduced as part of the daily maintenance tasks
- New environment variables added to `.env-dist`:
 - `DFE_REMOTE_*` database variables for use with the `dfe:move-instance` command
 - `DFE_RESET_DAYS_TO_KEEP` sets the number of days password resets are allowed to be valid
 - `DFE_ADS_ACTIVATE_BY_DAYS` sets the number of days that an instance may remain in a non-activated state before auto-deprovisioning occurs
 - `DFE_ADS_ACTIVATE_ALLOWED_EXTENDS` sets the number of _extensions_ (in days) allowed before an instance is auto-deprovisioned
 - `DFE_ADS_ALLOWED_INACTIVE_DAYS` sets the number of days that an instance may remain idle (post activation) before auto-deprovisioning occurs
- New default package installation setting (`provisioning.default-packages`) in `config/provisioning.php` 

### Updated
- Miscellaneous PSR-2 code formatting
- PhpDoc updates and cleanup 

## [1.0.26][v1.0.26]
### Updated
- Stats gathering service speed issue addressed
- UI fixes

## [1.0.25][v1.0.25]
### Updated
- Configuration file reorganization and changes
- Common library updates

## [1.0.20][v1.0.20]
### Updated
- Upgraded all components to Laravel 5.2
- Email templates now pull default subject/header from `config/notifications.php`
- Miscellaneous improvements

## [1.0.19][v1.0.19]
### Updated
- Refactor statistics method name to stop "recursive call"-caused segfaults
- Ensure content-type is set properly in calls to license server
- New generic email template

## [1.0.16][v1.0.16]
### Updated
- Internal service reorganization and improvements
- Debug logging removed in certain areas

## [1.0.15][v1.0.15]
### Added
- New `metrics_detail_t` table added. *Migration required*
- Metrics gathering system added to `dfe:metrics` command
- Metrics format standardized and versioned

## [1.0.13][v1.0.13]
### Updated
- `dfe:metrics` command updated to check deactivation status
- Removed extraneous debug logging

### Added
- Support for automated deactivation of instances

## [1.0.12][v1.0.12]
### Updated
- Removed duplicate **Logout** command from profile menu
- Instance delete ability added to instance console UI

## [1.0.11][v1.0.11]
### Added
- Instance create ability added to instance console UI
- Internationalization of UI strings for instance creation.
- Support for customisation via CSS and graphic assets during installation
- Standardized user creation via database model
- New console commands `dfe:blueprint`, `dfe:capsule`, `dfe:info`, `dfe:migrate-instance`, and `dfe:users` now available

### Updated
- Unnecessary required fields removed from server create
- Changed version constraint of PHPUNIT in composer.json to ensure only version 4.* is installed. Version 5.* requires PHP 5.6, which is not in the Ubuntu 14.04 repository.
- Instance limits system enhancements
- Miscellaneous UI fixes and enhancements

## [1.0.10][v1.0.10]
### Updated
- Changed version constraint of PHPUNIT in composer.json to ensure only version 4.* is installed. Version 5.* requires PHP 5.6, which is not in the Ubuntu 14.04 repository.

## [1.0.9][v1.0.9]
### Added
- Added `*-dist` copies of supplied branding assets.

### Updated
- Artisan commands **dfe:cluster**, **dfe:server**, and **dfe:mount** now have a "show" operation. 

## [1.0.8][v1.0.8]
### Added
- Migration to correct index on `job_result_t`

### Fixed
- Console registration failure corrected.

## [1.0.6][v1.0.6]
### Added
- **MigrateInstance** Artisan command
- **MigrateInstanceTest** unit test for **MigrateInstance** Artisan command
- Add package `dreamfactory/dfe-capsule` for instance encapsulation
- **Capsule** Artisan command
- **CapsuleTest** unit test for **Capsule** Artisan command

### Updated
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

[v1.0.27]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.26...1.0.27
[v1.0.26]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.25...1.0.26
[v1.0.25]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.20...1.0.25
[v1.0.20]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.19...1.0.20
[v1.0.19]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.16...1.0.19
[v1.0.16]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.15...1.0.16
[v1.0.15]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.14...1.0.15
[v1.0.13]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.12...1.0.13
[v1.0.12]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.11...1.0.12
[v1.0.11]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.10...1.0.11
[v1.0.10]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.9...1.0.10
[v1.0.9]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.8...1.0.9
[v1.0.8]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.6...1.0.8
[v1.0.6]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.2...1.0.6
[v1.0.2]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.1...1.0.2
[v1.0.1]: https://github.com/dreamfactorysoftware/dfe-console/compare/1.0.0...1.0.1
[v1.0.0]: https://github.com/dreamfactorysoftware/dfe-console/compare/master...1.0.0
[unstable]: https://github.com/dreamfactorysoftware/dfe-console/compare/develop...master
