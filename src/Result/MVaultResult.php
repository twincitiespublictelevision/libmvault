<?php

namespace LibMVault\Result;

use LibMVault\MVaultRecord;

/**
 * Class MVaultResult
 * @package LibMVault\Result
 */
class MVaultResult extends Result {

  /**
   * @param MVaultRecord $value
   * @return MVaultResult
   */
  public static function ok($value): Result {
    return parent::ok($value); // TODO: Change the autogenerated stub
  }

  /**
   * @param \Exception $err
   * @return MVaultResult
   */
  public static function err(\Exception $err): Result {
    return parent::err($err); // TODO: Change the autogenerated stub
  }

  /**
   * @return MVaultRecord
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
   * @return MVaultRecord|mixed
   */
  public function valueOr($fallback) {
    if ($this->isError()) {
      return $fallback;
    }

    return $this->_value;
  }
}