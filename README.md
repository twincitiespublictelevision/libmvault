# libmvault
[![CircleCI](https://circleci.com/gh/twincitiespublictelevision/libmvault/tree/master.svg?style=svg)](https://circleci.com/gh/twincitiespublictelevision/libmvault/tree/master)

`libmvault` is a small package containing a couple of classes for structured
usage of MVault data.

---

## Overview

Two main classes are provided: `MVaultRecord` and `PBSProfile`

Both support being created from JSON, array, or stdClass representations of
an MVault record. Creating a record returns a `MVaultResult` or `PBSProfileResult`
respectively. The result encapsulates either the created object or the error 
depending on the success of call.

## Usage

Documentation can be found at [https://twincitiespublictelevision.github.io/libmvault/](https://twincitiespublictelevision.github.io/libmvault/)

Result classes provide a return style for capturing the success or failure of a
given operation in a single return value. The value or error can then be extracted
from the result by the calling code and be conditionally used. An **ok** value
represents the success of an operation, whereas an **err** value represents the
failure of an operation.

When attempting to parse an array, stdClass, or string an ok will be returned if
the entire parsing of the record succeeds. If any of the steps fail then an err
is returned containing the failure.

An example of generic usage of a Result:

```php
$resultA = Result::ok("foo");
echo $resultA->value(); // foo

$resultB = Result::err(new \Exception("Bar error");
echo $resultB->value(); // PHP Fatal error:  Uncaught exception ...
```

To safely handle a result and extract its value the caller can use either
conditionals or try / catch syntax

```php
$resultA = Result::ok("foo");

if ($resultA->isOk()) {
  echo $result->value(); // foo
} else {
  // ...
}

$resultB = Result::err(new \Exception("Bar error");

try {
  echo $resultB->value();
} catch (\Exception $e) {
  echo $e->getMessage(); // Bar error
}
```

## Requirements

* PHP >= 7.1

## Installing

1. Add to the **repositories** key of your **composer.json** file:
```
{
  "type": "vcs",
  "url": "https://github.com/twincitiespublictelevision/libmvault.git"
}
```

2. Run `composer require twincitiespublictelevision/libmvault:dev-master` to pull in the package