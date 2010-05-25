# DB Plumber

## Maintainer Contact
 * Andreas Piening <andreas (at) silverstripe (dot) com>

## Requirements
 * SilverStripe 2.4 or newer


## Installation
 1. [do the usual](http://doc.silverstripe.org/modules#installation)

## Description

 * Ever wanted to just browse a database table or CRUD records, BUT you didn't have a suitable SQL client at hand?
 * Ever wanted to just try out or dry run a SQL command, BUT your SQL client didn't use ANSI double quotes for table and field names? (phpMyAdmin, SQL Server Management Studio)
 * Ever wanted to test a SQLite command, BUT your SQL client uses a different SQLite version which behaves differently?

This module provides tools to administer the database that drives your SilverStripe installation.
It is not as powerful as phpMyAdmin but it is very lightweight and works with all supported database adapters: MySQL, MSSQL, Postgres and SQLite
This module is designed for developers and in production has to be used with caution if at all.

## Open Issues

 * check permission on access

## Planned Features

 * CRUD records in the table browser
 * import/export tables/db
 * backup/restore
