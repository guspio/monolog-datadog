[![Build Status](https://img.shields.io/travis/guspio/monolog-datadog?branch=master&style=flat-square)](https://travis-ci.org/guspio/monolog-datadog)
[![Latest Stable Version](https://img.shields.io/packagist/v/guspio/monolog-datadog.svg?style=flat-square)](https://packagist.org/packages/guspio/monolog-datadog)
![PHP from Packagist](https://img.shields.io/packagist/php-v/guspio/monolog-datadog?style=flat-square)
![Licence](https://img.shields.io/packagist/l/guspio/monolog-datadog?style=flat-square)

# Datadog Monolog integration


MonologDatadog is a Datadog Log handler for Monolog. It enables you to send logs directly to HTTP with Curl.

## Requirements

- [Datadog Account](https://www.datadoghq.com)
- [Datadog Api Key](https://app.datadoghq.com/account/settings#api)
- Php 7.1+
- Php-curl

## Features

- Send Monolog logs directly to HTTP with Curl
- Automatically JSON Formatted

## Installation

Install the latest version with

```
composer require guspio/monolog-datadog
```


### Basic Usage

```php
<?php

use Monolog\Logger;
use MonologDatadog\Handler\DatadogHandler;

// Add your [Datadog Api Key]
$apiKey = 'YOUR-DATADOG-API-KEY';

$attributes = [
    'hostname' => 'YOUR_HOSTNAME', // Default: $_SERVER['SERVER_NAME']
    'source' => 'php', // Default: php
    'service' => 'YOUR-SERVICE' // Default: Monolog channel name
];

$logger = new Logger('datadog-channel');

$datadogLogs = new DatadogHandler($apiKey, $attributes, Logger::INFO);

$logger->pushHandler($datadogLogs);

$logger->info('i am an info');
$logger->warning('i am a warning..');
$logger->error('i am an error ');
$logger->notice('i am a notice');
$logger->emergency('i am an emergency');
```
