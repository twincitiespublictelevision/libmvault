<?php

namespace LibMVault;

use LibMVault\Result\PBSProfileResult;

/**
 * Class PBSProfile
 * @package LibMVault
 */
class PBSProfile implements \JsonSerializable {

  const REQUIRED = [
    'retrieval_status'
  ];

  const SUCCESS_REQUIRED = [
    'first_name', 'last_name', 'UID', 'email', 'login_provider'
  ];

  /**
   * @var string
   */
  private $_firstName;

  /**
   * @var string
   */
  private $_lastName;

  /**
   * @var string
   */
  private $_pid;

  /**
   * @var string
   */
  private $_email;

  /**
   * @var string
   */
  private $_loginProvider;

  /**
   * PBSProfile constructor.
   * @param string $firstName
   * @param string $lastName
   * @param string $uid
   * @param string $email
   * @param string $loginProvider
   */
  private function __construct(string $firstName, string $lastName, string $pid, string $email, string $loginProvider) {
    $this->_firstName = $firstName;
    $this->_lastName = $lastName;
    $this->_pid = $pid;
    $this->_email = $email;
    $this->_loginProvider = $loginProvider;
  }

  /**
   * @param string $record
   * @return PBSProfileResult
   */
  public static function fromJSON(string $record): PBSProfileResult {
    try {
      $parsed = ex_json_decode($record);
      return self::fromStdClass($parsed);
    } catch (\Exception $e) {
      return PBSProfileResult::err($e);
    }
  }

  /**
   * @param array $record
   * @return PBSProfileResult
   */
  public static function fromArray(array $record): PBSProfileResult {

    // Records do not get terribly large, so for simplicity we encode and then
    // decode from JSON at the cost of a little performance
    try {
      return self::fromJSON(ex_json_encode($record));
    } catch (\Exception $e) {
      return PBSProfileResult::err($e);
    }
  }

  /**
   * @param \stdClass $record
   * @return PBSProfileResult
   */
  public static function fromStdClass(\stdClass $record): PBSProfileResult {
    foreach (self::REQUIRED as $req) {
      if (!property_exists($record, $req)) {
        return PBSProfileResult::err(new \InvalidArgumentException("Malformed PBS Profile. {$req} field is missing."));
      }
    }

    if ($record->retrieval_status->status === 500) {
      return PBSProfileResult::err(new \Exception($record->retrieval_status->message));
    }

    if ($record->retrieval_status->status !== 200) {
      return PBSProfileResult::err(
        new \Exception(
          "PBS returned unknown error. Code: {$record->retrieval_status->status}. Message: {$record->retrieval_status->message}"
        )
      );
    }

    foreach (self::SUCCESS_REQUIRED as $req) {
      if (!property_exists($record, $req)) {
        return PBSProfileResult::err(new \InvalidArgumentException("Malformed PBS Profile. {$req} field is missing."));
      }
    }

    return PBSProfileResult::ok(
      new PBSProfile(
        $record->first_name,
        $record->last_name,
        $record->UID,
        $record->email,
        $record->login_provider
      )
    );
  }

  /**
   * @return array
   */
  public function toArray(): array {
    return [
      'first_name' => $this->getFirstName(),
      'last_name' => $this->getLastName(),
      'UID' => $this->getPID(),
      'email' => $this->getEmail(),
      'login_provider' => $this->getLoginProvider()
    ];
  }

  /**
   * @return \stdClass
   */
  public function toStdClass(): \stdClass {
    return json_decode(json_encode($this));
  }

  /**
   * @return string
   */
  public function getFirstName(): string {
    return $this->_firstName;
  }

  /**
   * @return string
   */
  public function getLastName(): string {
    return $this->_lastName;
  }

  /**
   * @return string
   */
  public function getPID(): string {
    return $this->_pid;
  }

  /**
   * @return string
   */
  public function getEmail(): string {
    return $this->_email;
  }

  /**
   * @return string
   */
  public function getLoginProvider(): string {
    return $this->_loginProvider;
  }

  /**
   * @return mixed
   */
  public function jsonSerialize() {
    return $this->toArray();
  }
}