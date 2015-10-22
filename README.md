A simple DateTime library for PHP
=================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-date/v/stable.svg)](https://packagist.org/packages/romeOz/rock-date)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-date/downloads.svg)](https://packagist.org/packages/romeOz/rock-date)
[![Build Status](https://travis-ci.org/romeOz/rock-date.svg?branch=master)](https://travis-ci.org/romeOz/rock-date)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-date/badge.svg?branch=master)](https://coveralls.io/r/romeOz/rock-date?branch=master)
[![License](https://poser.pugx.org/romeOz/rock-date/license.svg)](https://packagist.org/packages/romeOz/rock-date)

Features
-------------------

 * Supports many formats (`m/d/Y`, `m/d/Y g:i A` and other)
 * Customization of formats and options
 * i18n support
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Installation
-------------------

From the Command Line:

`composer require romeoz/rock-date:`

In your composer.json:

```json
{
    "require": {
        "romeoz/rock-date": "*"
    }
}
```

Quick Start
-------------------

```php
use rock\date\DateTime;

(new DateTime)->format(); // output: current date in the format Y-m-d H:i:s

// default format 
(new DateTime)->isoDate(); // output: current date in the format Y-m-d

// modify date
DateTime::set('1988-11-12')->date(); //output: 11/12/1988
```

####Custom format

```php
$datetime = DateTime::set('1988-11-12');
$datetime->addCustomFormat('shortDate', 'j / F / Y');

$datetime->shortDate(); // output: 12 / November / 1988
```

####Custom option format

```php
$datetime = new DateTime('1988-11-12');
$datetime->addFormatOption('ago', function (DateTime $datetime) {
    return floor((time() - $datetime->getTimestamp()) / 86400) . ' days ago';
});

$datetime->format('d F Y, ago'); // output: 12 November 1988, 9574 days ago
```

####i18n

```php
$dateTime = new DateTime('1988-11-12');
$dateTime->locale('ru');

$dateTime->format('j  F  Y'); // output: 12  ноября  1988 
```

Documentation
-------------------

 * [Custom locales](https://github.com/romeOz/rock-date/blob/master/docs/custom-locales.md)


Requirements
-------------------
 * **PHP 5.4+**

License
-------------------

The DateTime library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).