<?php

namespace LibMVault\Result;

/**
 * Class Result
 * @package LibMVault
 */
class Result {

  /**
   * @var mixed|null
   */
  protected $_value;

  /**
   * @var \Exception|null
   */
  protected $_e;

  /**
   * Result constructor.
   * @param mixed|null $value
   * @param \Exception|null $e
   */
  private function __construct($value = null, \Exception $e = null) {
    $this->_value = $value;
    $this->_e = $e;
  }

  /**
   * @param mixed $value
   * @return Result
   */
  public static function ok($value): Result {
    return new Result($value, null);
  }

  /**
   * @param \Exception $err
   * @return Result
   */
  public static function err(\Exception $err): Result {
    return new Result(null, $err);
  }

  /**
   * @return bool
   */
  public function isError(): bool {
    return $this->_e !== null;
  }

  /**
   * @return bool
   */
  public function isOk(): bool {
    return !$this->isError();
  }

  /**
   * @return mixed
   * @throws \Exception
   */
  public function value() {
    if ($this->isError()) {
      throw $this->_e;
    }

    return $this->_value;
  }

  /**
   * @return \Exception|null
   */
  public function getErr() {
    return $this->_e;
  }

  /**
   * @param mixed $fallback
   * @return mixed
   */
  public function valueOr($fallback) {
    if ($this->isError()) {
      return $fallback;
    }

    return $this->_value;
  }
}