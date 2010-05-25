*DB Plumber*

- Ever wanted to just browse a database table or CRUD records, BUT you didn't have a suitable SQL client at hand?
- Ever wanted to just try out or dry run a SQL command, BUT your SQL client didn't use ANSI double quotes for table and field names? (phpMyAdmin, SQL Server Management Studio)
- Ever wanted to test an SQLite command, BUT your SQL client uses a different SQLite version which behaves differently?

This module provides tools to administer the database that drives your SilverStripe installation.
It is very lightweight and not as powerful as phpMyAdmin but it works with all supported database adapters: MySQL, MSSQL, Postgres and SQLite
This module is designed for developers and in production has to be used with caution if at all.

*toFIX*

- *check permission on access*

*toENHANCE*

- add more (adapterspecific) tools, e.g. export
- create models for table/db/fields/etc, at the moment everything is in the controller :(
