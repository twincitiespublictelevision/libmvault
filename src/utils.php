<?php

namespace LibMVault;

/**
 * JSON handling functions from https://github.com/guzzle/guzzle
 */

/**
  Copyright (c) 2011-2016 Michael Dowling, https://github.com/mtdowling <mtdowling@gmail.com>

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

/**
 * Wrapper for json_decode that throws when an error occurs.
 *
 * @param string $json    JSON data to parse
 * @param bool $assoc     When true, returned objects will be converted
 *                        into associative arrays.
 * @param int    $depth   User specified recursion depth.
 * @param int    $options Bitmask of JSON decode options.
 *
 * @return mixed
 * @throws \InvalidArgumentException if the JSON cannot be decoded.
 * @link http://www.php.net/manual/en/function.json-decode.php
 */
function ex_json_decode($json, $assoc = false, $depth = 512, $options = 0)
{
  $data = \json_decode($json, $assoc, $depth, $options);
  if (JSON_ERROR_NONE !== json_last_error()) {
    throw new \InvalidArgumentException(
      'json_decode error: ' . json_last_error_msg());
  }

  return $data;
}

/**
 * Wrapper for JSON encoding that throws when an error occurs.
 *
 * @param mixed $value   The value being encoded
 * @param int    $options JSON encode option bitmask
 * @param int    $depth   Set the maximum depth. Must be greater than zero.
 *
 * @return string
 * @throws \InvalidArgumentException if the JSON cannot be encoded.
 * @link http://www.php.net/manual/en/function.json-encode.php
 */
function ex_json_encode($value, $options = 0, $depth = 512)
{
  $json = \json_encode($value, $options, $depth);
  if (JSON_ERROR_NONE !== json_last_error()) {
    throw new \InvalidArgumentException(
      'json_encode error: ' . json_last_error_msg());
  }

  return $json;
}