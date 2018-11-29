<?php

namespace LibMVault;

use LibMVault\Result\PBSProfileResult;

/**
 * Class PBSProfile
 *
 * Represents the response from the PBS Profile sub request in an activated
 * MVault record.
 *
 * A retrieval status object and a UID are always present. The remaining profile
 * information is only required if the retrieval status object represents a
 * successful request.
 *
 * @package LibMVault
 */
class PBSProfile implements \JsonSerializable {

  /**
   * @var array
   */
  const REQUIRED = [
    'retrieval_status', 'UID'
  ];

  /**
   * @var array
   */
  const SUCCESS_REQUIRED = [
    'first_name', 'last_name', 'email', 'login_provider'
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
   * @param string $pid
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
      'retrieval_status' => $this->getRetrievalStatus(),
      'UID' => $this->getPID()
    ];

    if ($this->getRetrievalStatus()->getStatus() === 200) {
      $data['first_name'] = $this->getFirstName();
      $data['last_name'] = $this->getLastName();
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
   * Gets the first name of the user that activated this MVault record. Returns
   * a string if this object represents a successful profile lookup request,
   * otherwise null is returned.
   *
   * @return string|null
   */
  public function getFirstName(): ?string {
    return $this->_firstName;
  }

  /**
   * Gets the last name of the user that activated this MVault record. Returns
   * a string if this object represents a successful profile lookup request,
   * otherwise null is returned.
   *
   * @return string|null
   */
  public function getLastName(): ?string {
    return $this->_lastName;
  }

  /**
   * Gets the PID of the user that activated this MVault record. Represented by
   * the UID field in an MVault API response.
   *
   * @return string
   */
  public function getPID(): string {
    return $this->_pid;
  }

  /**
   * Gets the email of the user that activated this MVault record. Returns
   * a string if this object represents a successful profile lookup request,
   * otherwise null is returned.
   *
   * @return string|null
   */
  public function getEmail(): ?string {
    return $this->_email;
  }

  /**
   * Gets the login provider of the user that activated this MVault record. This
   * is the value reported by PBS and takes one of three values: PBS, Google, or
   * Facebook. Returns a string if this object represents a successful profile
   * lookup request, otherwise null is returned.
   *
   * @return string|null
   */
  public function getLoginProvider(): ?string {
    return $this->_loginProvider;
  }

  /**
   * Gets the retrieval status object describing the the success of the PBS
   * profile sub-request.
   *
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