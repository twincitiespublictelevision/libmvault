# libmvault

`libmvault` is a small package containing a couple of classes for structured
usage of MVault data.

---

## Overview

Two main classes are provided: `MVaultRecord` and `PBSProfile`

Both support being created from JSON, array, or stdClass representations of
an MVault record. Creating a record returns a `MVaultResult` or `PBSProfileResult`
respectively. The result encapsulates either the created object or the error 
depending on the success of call.

## Requirements

* PHP >= 7.1

## Installing