# DB Plumber

## Maintainer Contact
 * Andreas Piening <andreas (at) silverstripe (dot) com>

## Requirements
 * SilverStripe 2.4 or newer


## WARNING !

Normally Silverstripe/Sapphire is perfectly capable of managing its own database. By manually changing records or the db schema through DBPlumber you can easily break your SilverStripe installation. Use DBPlumber carefully and at your own risk!
This module is designed for developers and not for content authors. That is why DBPlumber requires ADMIN rights.
**Do not use in production!**

## Installation

 1. follow the usual [module installation process](http://doc.silverstripe.org/modules#installation)

## Setup

Use the following static properties and methods to control DBPlumber

### Limit DBPlumber to certain environments

	DatabaseBrowser::$trusted_envs = array('test', 'dev');

### Limit DBPlumber to trusted IPs

The IPs are matched against $_SERVER['REMOTE_ADDR'] so if it is misbehaving check your $_SERVER['REMOTE_ADDR'] which sometimes return unexpected values like ::1

	DatabaseBrowser::$trusted_ips = array('127.0.0.1');

### Deactivate DBPlumber, useful in combination with _ss_environment.php

	DatabaseBrowser::$activated = false;

### Hide DBPlumber from the CMS menu. Useful if DBPlumber is accessible but

If you don't want it to appear in the CMS but only access it through http://your-domain.com/admin/dbplumber

	DatabaseBrowser::hide_from_menu();

### Hide database config from info tab

	DatabaseBrowser::$expose_config = false;

## Description

 * Ever wanted to just browse a database table or CRUD records, BUT you didn't have a suitable SQL client at hand?
 * Ever wanted to access your database running on a remote host, BUT the database engine allows access only for local connections?
 * Ever wanted to just try out or dry run a SQL command, BUT your SQL client didn't use ANSI double quotes for table and field names? (phpMyAdmin, SQL Server Management Studio)
 * Ever wanted to test a SQLite command, BUT your SQL client uses a different SQLite version which behaves differently?

This module provides tools to manage the database that drives your SilverStripe installation.
It is not as powerful as phpMyAdmin but it is very lightweight and works with all supported database adapters: MySQL, MSSQL, Postgres and SQLite

## Features

 * All features are accessible through a new CMS section called DBPlumber. Access is restricted to admins.
 * Use SQL tab to enter any sql query and send it to your database. SELECT results are displayed in a clear table. Use indentation to clean up your SQL commands.
 * The UI lists all tables in the database, you can browse records by table, the records are paginated and sortable.
 * Click on records to select them for deletion or double click to edit them.
 * Use the form tab to insert a new record to a table.
 * Export tables
 * Bulk execute SQL files/backups, now supports Zlib compression
 * Truncate or drop tables
 * Access to DBPlumber is limited to ADMINs and can be further restricted to trusted IPs, env types (e.g. 'dev' and 'test' only) and it can be hidden from the CMS main menu

## Open Issues

 * Misbehaving js/css in IE

## Planned Features

 * saving of custom queries

## Feedback

This module is in beta state. Please help me to improve it by submitting your feedback/bug reports/support requests/suggestions. Thanks