eLife API Problem PHP
=====================

[![Build Status](https://ci--alfred.elifesciences.org/buildStatus/icon?job=library-api-problem-php)](https://ci--alfred.elifesciences.org/job/library-api-problem-php/)

This library provides a [RFC 7807 handler](https://tools.ietf.org/html/rfc7807) for the eLife Sciences applications.

Dependencies
------------

* [Composer](https://getcomposer.org/)
* PHP 7

Installation
-------------

`composer require elife/api-problem`

Set up
------

### Silex

```php
use eLife\ApiProblem\Silex\ApiProblemProvider;

$app->register(new ApiProblemProvider());
```

Exception details (eg stacktrace) will be included based on the value of `$app['debug']`. This can be overridden by setting `$app['api_problem.factory.include_exception_details']` to `true` or `false`.

Running the tests
-----------------

`vendor/bin/phpunit`
