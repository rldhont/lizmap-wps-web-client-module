# Changelog

<!--
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).
-->

## Unreleased

## 0.5.3 - 2025-06-20

### Fixed 

* Check WPS project config file exists

## 0.5.2 - 2025-05-12

### Added

* French language
* Admin: Manage QGIS Style files for QGIS Models
* UI: Mandatory processing inputs
* WPS panel image
* max Lizmap Web Client version 3.9.*

### Fixed

* GetResults response: OWS Url replacement
* GetResults response: Empty OWS Url
* JS variables for WPS


## 0.5.1 - 2024-07-23

### Changed

* Confirm delete & allow file update

### Fixed

* OL serverType qgis

## 0.5.0 - 2024-07-22

### Changed

* Dependencies : Lizmap Web Client 3.8 Minimum version

## 0.3.1 - 2024-07-11

### Fixed

* Bugfix JS: Check wps elements are loaded

## 0.3.0 - 2024-02-26

### Fixed

* Fixing the delete method of results controller
* Support outputs files and jobs
* UI: No output displayed for undefined layer name
* UI: The triangle before title not cliquable in results
* UI: Hide WPS if no algorithm available
* Fixing the way to manage selection for FeatureSource
* Fixing the results controller
* UI: Adding status in log table

### Added

* Tests: enhancing Test input file destination algorithm
* UI: Display file outputs
* UI: Select default layer
* UI: Update field parameter at startup
* Config: Field parameter can be restricted
* Tests: Algorithm to convert feature source to vector layer
* Support WMS DescribeLayer request
* Using Expression filter when it is provided
* CSS: Add id to processing input control-group
* new config to restrict access to WPS
* Support X-Job-Realm for use
* Support Administrator realm token
* Tests: enhancing Test environment to work with lizmap 3.6
* Job label based on input value
* Select the algorithm if only one is available
* UI: Admin page to upload model3 files
* UI: Admin page to manage WPS enabling restrictions on projects

## 0.2.0 - 2022-11-22

* Fix path to dataviz for Lizmap >= 3.4
* Update to QGIS Server 3.16
* Refactor HTTP calls
* Some cleanup in the code
* Update the composer.json file for Packagist
* Start some tests using the Cypress framework
* Update php-cs-fixer to 3.8.0
* Experimental compatibility with Lizmap 3.6
* Support Extent input
* Support Point input

## 0.1.1 - 2021-03-31

## 0.1.0 - 2020-11-25
