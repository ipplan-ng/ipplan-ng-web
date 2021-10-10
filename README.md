<p align="center"><a href="https://github.com/ipplan-ng" target="_blank"><img src="https://raw.githubusercontent.com/ipplan-ng/art/main/web/logo-banner/ipplan-ng-logo-banner.png" width="400"></a></p>

## About IPplan-NG

IPplan-NG is a fork of [IPplan](http://iptrack.sourceforge.net/) 4.92b. The author of IPplan has stopped development so IPplan was stuck on PHP 5. There are still people using IPplan but need to upgrade to PHP 7+ so IPplan-NG was started. The database schema has been left untouched so IPplan-NG could be a drop in replacment for an existing IPplan install. Future releases of IPplan-NG will start changing the database schema to take advantage of new database features such as the PostgreSQL inet type.

## Status

IPplan-NG is in the testing phase right now. The core PHP code has been re-written to run on PHP 7 and eventually will run on PHP 8. There are a lot of features in IPplan that need tested to make sure they still work correctly on newer versions of PHP and newer database servers. 

## Changes

- PHP code re-written to run under PHP 7.
- ADOdb upgraded to version 5.20.20.
- Change HTML output to HTML5.
- Use CSS for layout instead of HTML attributes.

## Documentation

The documentation is in the process of being updated. You may still find old versions from origianl IPPlan until they are updated. Documentation has been moved to the docs/ directory.
