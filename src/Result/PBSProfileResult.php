<?php

namespace LibMVault\Result;

use LibMVault\PBSProfile;

/**
 * Class PBSProfileResult
 * @package LibMVault\Result
 */
class PBSProfileResult {

  /**
   * @var Result
   */
  protected $res;

  /**
   * PBSProfileResult constructor.
   * @param Result $res
   */
  private function __construct(Result $res) {
    $this->res = $res;
  }

  /**
   * @param PBSProfile|null $value
   * @return PBSProfileResult
   */
  public static function ok(?PBSProfile $value): PBSProfileResult {
    return new PBSProfileResult(Result::ok($value));
  }

  /**
   * @param \Exception $err
   * @return PBSProfileResult
   */
  public static function err(\Exception $err): PBSProfileResult {
    return new PBSProfileResult(Result::err($err));
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
   * @return PBSProfile|null
   * @throws \Exception
   */
  public function value(): ?PBSProfile {
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
   * @return PBSProfile|null|mixed
   */
  public function valueOr($fallback) {
    return $this->res->valueOr($fallback);
  }
}