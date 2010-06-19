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

## Description

 * Ever wanted to just browse a database table or CRUD records, BUT you didn't have a suitable SQL client at hand?
 * Ever wanted to access your database running on a remote host, BUT the database engine allows access only for local connections?
 * Ever wanted to just try out or dry run a SQL command, BUT your SQL client didn't use ANSI double quotes for table and field names? (phpMyAdmin, SQL Server Management Studio)
 * Ever wanted to test a SQLite command, BUT your SQL client uses a different SQLite version which behaves differently?

This module provides tools to manage the database that drives your SilverStripe installation.
It is not as powerful as phpMyAdmin but it is very lightweight and works with all supported database adapters: MySQL, MSSQL, Postgres and SQLite

## Open Issues

 * Tiny css glitches in Safari/IE8 (IE6-7 not yet tested)

## Planned Features

 * import/export tables/db
 * backup/restore
