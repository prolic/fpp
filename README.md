# FPP

## Functional PHP Preprocessor - Immutable data type generator 

This library can generate immutable data types based on fpp definitions. The syntax is inspired by Haskell.

[![Build Status](https://travis-ci.org/prolic/fpp.svg?branch=master)](https://travis-ci.org/prolic/fpp)

## Sponsor

If you want to support my work, become a patron at [patreon.com/prolic](https://www.patreon.com/notifications).

## Credits

[Marcello Duarte](https://github.com/MarcelloDuarte/) created the [ParserCombinators](https://github.com/MarcelloDuarte/ParserCombinators/) project in 2017.
The rewrite of this library is heavily inspired by it and reuses some of its base functions.

## Docs

Coming soon...

## Install

```console
composer require prolic/fpp dev-master
```

## Usage

Disable xdebug or increase xdebug.max_nesting_level in your php.ini file.

```php
php vendor/bin/fpp.php <source dir or file>
```

## Generate configuration

```php
php vendor/bin/fpp.php --gen-config
```

You can then modify the config file to adjust to your needs.

## Changes from 0.1.0 Release

This library has been rewritten from scratch. If you want to use the old version, pin your composer requirement to 0.1.0.

The master branch is not compatible at all.
