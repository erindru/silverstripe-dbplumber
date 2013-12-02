# DB Plumber

This is a fork of smindel/silverstripe-dbplumber that strips out everything except the useful RemoveArtefactsTask abd FixLowercaseTableNamesTask

This fork also provides composer support

## Requirements
 * SilverStripe 3.1 or newer

## DB Adapter Support
 * MySQL
 * SQLite
 * Postgres
 * SQL Server
 * Oracle (experimental)

## WARNING !

Normally Silverstripe/Sapphire is perfectly capable of managing its own database. By manually changing records or the db schema through DBPlumber you can easily break your SilverStripe installation. Use DBPlumber carefully and at your own risk!
This module is designed for developers and not for content authors. That is why DBPlumber requires ADMIN rights.
**Do not use in production!**

## Installation

Install via composer

## Usage

Browse to `/dev/tasks` and run the RemoveArtesfactsTask