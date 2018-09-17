<?php

namespace LibMVault\Result;

use LibMVault\RetrievalStatus;

/**
 * Class RetrievalStatusResult
 * @package LibMVault\Result
 */
class RetrievalStatusResult {

  /**
   * @var Result
   */
  protected $res;

  /**
   * RetrievalStatusResult constructor.
   * @param Result $res
   */
  private function __construct(Result $res) {
    $this->res = $res;
  }

  /**
   * @param RetrievalStatus|null $value
   * @return RetrievalStatusResult
   */
  public static function ok(?RetrievalStatus $value): RetrievalStatusResult {
    return new RetrievalStatusResult(Result::ok($value));
  }

  /**
   * @param \Exception $err
   * @return RetrievalStatusResult
   */
  public static function err(\Exception $err): RetrievalStatusResult {
    return new RetrievalStatusResult(Result::err($err));
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
   * @return RetrievalStatus|null
   * @throws \Exception
   */
  public function value(): ?RetrievalStatus {
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
   * @return RetrievalStatus|null|mixed
   */
  public function valueOr($fallback) {
    return $this->res->valueOr($fallback);
  }
}