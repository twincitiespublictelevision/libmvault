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
   * @var string|null
   */
  private $_firstName;

  /**
   * @var string|null
   */
  private $_lastName;

  /**
   * @var string|null
   */
  private $_pid;

  /**
   * @var string|null
   */
  private $_email;

  /**
   * @var string|null
   */
  private $_loginProvider;

  /**
   * @var RetrievalStatus
   */
  private $_retrievalStatus;

  /**
   * PBSProfile constructor.
   * @param string $firstName
   * @param string $lastName
   * @param string $uid
   * @param string $email
   * @param string $loginProvider
   */
  private function __construct(?string $firstName, ?string $lastName, ?string $pid, ?string $email, ?string $loginProvider, RetrievalStatus $status) {
    $this->_firstName = $firstName;
    $this->_lastName = $lastName;
    $this->_pid = $pid;
    $this->_email = $email;
    $this->_loginProvider = $loginProvider;
    $this->_retrievalStatus = $status;
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

    $retrievalStatusResult = RetrievalStatus::fromStdClass($record->retrieval_status);

    if ($retrievalStatusResult->isError()) {
      return PBSProfileResult::err($retrievalStatusResult->getErr());
    } else {
      $retrievalStatus = $retrievalStatusResult->value();
    }

    if ($retrievalStatus->getStatus() === 200) {
      foreach (self::SUCCESS_REQUIRED as $req) {
        if (!property_exists($record, $req)) {
          return PBSProfileResult::err(new \InvalidArgumentException("Malformed PBS Profile. {$req} field is missing."));
        }
      }
    }

    return PBSProfileResult::ok(
      new PBSProfile(
        $record->first_name ?? null,
        $record->last_name ?? null,
        $record->UID ?? null,
        $record->email ?? null,
        $record->login_provider ?? null,
        $retrievalStatus
      )
    );
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $data = [
      'retrieval_status' => $this->getRetrievalStatus()
    ];

    if ($this->getRetrievalStatus()->getStatus() === 200) {
      $data['first_name'] = $this->getFirstName();
      $data['last_name'] = $this->getLastName();
      $data['UID'] = $this->getPID();
      $data['email'] = $this->getEmail();
      $data['login_provider'] = $this->getLoginProvider();
    }

    return $data;
  }

  /**
   * @return \stdClass
   */
  public function toStdClass(): \stdClass {
    return json_decode(json_encode($this));
  }

  /**
   * @return string|null
   */
  public function getFirstName(): ?string {
    return $this->_firstName;
  }

  /**
   * @return string|null
   */
  public function getLastName(): ?string {
    return $this->_lastName;
  }

  /**
   * @return string|null
   */
  public function getPID(): ?string {
    return $this->_pid;
  }

  /**
   * @return string|null
   */
  public function getEmail(): ?string {
    return $this->_email;
  }

  /**
   * @return string|null
   */
  public function getLoginProvider(): ?string {
    return $this->_loginProvider;
  }

  /**
   * @return RetrievalStatus
   */
  public function getRetrievalStatus(): RetrievalStatus {
    return $this->_retrievalStatus;
  }

  /**
   * @return mixed
   */
  public function jsonSerialize() {
    return $this->toArray();
  }
}