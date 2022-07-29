# Commerce Order Notes Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.0 - 2022-07-20
### Added
- Compatibility with Craft 4
- Compatibility with Craft Commerce 4

## 2.0.7 - 2021-12-16
### Fixed
- Commerce 3 - note currency formatter fix

## 2.0.6 - 2021-12-08
### Changed
- Now requires Commerce 3

## 2.0.5 - 2021-11-23
### Fixed
- Commerce 3 order tabs fix

## 2.0.4 - 2020-05-02
### Fixed
- adding products
- recalc order improvements

## 2.0.3 - 2020-04-23
### Changed
- UI update

## 2.0.2 - 2020-04-22
### Changed
-	Value can now be a negative value
-	UI updates
-	Manual note can now be used on completed orders
-	Notes that have a 0 value don't recalculate the order
-	coupon code gets removed and reapplied when recalculating the order so any restictions are intact


## 2.0.1 - 2020-04-10
### Changed
-	UI updates

## 2.0.0 - 2020-03-10

### Changed

-   Now works with craft commerce 3

## 1.1.2 - 2020-01-13

### Fixed

-   Manual adjuster

## 1.1.1 - 2020-01-11

### Fixed

-   migration script

## 1.1.0 - 2020-01-11

### Added

-   Change email type
-   afterDelete call on model types
-   register note types event

## 1.0.7 - 2019-12-06

### Changed

-   note model return user even if disabled

### Added

-   notes service getNotes now accepts an array of conditions to filter notes on.

## 1.0.6 - 2019-08-29

### Changed

-   Plugin icon

## 1.0.5 - 2019-05-20

### Fixed

-   Only remark order as complete if already complete.

## 1.0.0 - 2018-12-13

### Added

-   Initial release
