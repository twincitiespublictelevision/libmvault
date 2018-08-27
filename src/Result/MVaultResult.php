<?php

namespace LibMVault\Result;

use LibMVault\MVaultRecord;

/**
 * Class MVaultResult
 * @package LibMVault\Result
 */
class MVaultResult extends Result {

  /**
   * @param MVaultRecord|null $value
   * @return MVaultResult
   */
  public static function ok($value): Result {
    return parent::ok($value);
  }

  /**
   * @param \Exception $err
   * @return MVaultResult
   */
  public static function err(\Exception $err): Result {
    return parent::err($err);
  }

  /**
   * @return MVaultRecord|null
   * @throws \Exception
   */
  public function value() {
    if ($this->isError()) {
      throw $this->_e;
    }

    return $this->_value;
  }

  /**
   * @param mixed $fallback
   * @return MVaultRecord|null|mixed
   */
  public function valueOr($fallback) {
    if ($this->isError()) {
      return $fallback;
    }

    return $this->_value;
  }
}