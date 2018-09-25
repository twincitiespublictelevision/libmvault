<?php

namespace LibMVault\Result;

use LibMVault\MVaultRecord;

/**
 * Class MVaultResult
 * @package LibMVault\Result
 */
class MVaultResult {

  /**
   * @var Result
   */
  protected $res;

  /**
   * MVaultResult constructor.
   * @param Result $res
   */
  private function __construct(Result $res) {
    $this->res = $res;
  }

  /**
   * @param MVaultRecord $value
   * @return MVaultResult
   */
  public static function ok(MVaultRecord $value): MVaultResult {
    return new MVaultResult(Result::ok($value));
  }

  /**
   * @param \Exception $err
   * @return MVaultResult
   */
  public static function err(\Exception $err): MVaultResult {
    return new MVaultResult(Result::err($err));
  }

  /**
   * @return bool
   */
  public function isOk(): bool {
    return $this->res->isOk();
  }

  /**
   * @return bool
   */
  public function isError(): bool {
    return $this->res->isError();
  }

  /**
   * @return MVaultRecord
   * @throws \Exception
   */
  public function value(): MVaultRecord {
    return $this->res->value();
  }

  /**
   * @return \Exception|null
   */
  public function getErr() {
    return $this->res->getErr();
  }

  /**
   * @param mixed $fallback
   * @return MVaultRecord|null|mixed
   */
  public function valueOr($fallback) {
    return $this->res->valueOr($fallback);
  }
}