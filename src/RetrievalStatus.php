<?php

namespace LibMVault;

use LibMVault\Result\RetrievalStatusResult;

/**
 * Class RetrievalStatus
 * @package LibMVault
 */
class RetrievalStatus implements \JsonSerializable {

  const REQUIRED = [
    'status', 'message'
  ];

  const FAILURE_REQUIRED = [
    'UID'
  ];

  /**
   * @var integer
   */
  private $_status;

  /**
   * @var string
   */
  private $_message;

  /**
   * @var string|null
   */
  private $_UID;

  /**
   * RetrievalStatus constructor.
   * @param string $firstName
   * @param string $lastName
   * @param string $uid
   * @param string $email
   * @param string $loginProvider
   */
  private function __construct(int $status, string $message, ?string $UID) {
    $this->_status = $status;
    $this->_message = $message;
    $this->_UID = $UID;
  }

  /**
   * @param string $record
   * @return RetrievalStatusResult
   */
  public static function fromJSON(string $record): RetrievalStatusResult {
    try {
      $parsed = ex_json_decode($record);
      return self::fromStdClass($parsed);
    } catch (\Exception $e) {
      return RetrievalStatusResult::err($e);
    }
  }

  /**
   * @param array $record
   * @return RetrievalStatusResult
   */
  public static function fromArray(array $record): RetrievalStatusResult {

    // Records do not get terribly large, so for simplicity we encode and then
    // decode from JSON at the cost of a little performance
    try {
      return self::fromJSON(ex_json_encode($record));
    } catch (\Exception $e) {
      return RetrievalStatusResult::err($e);
    }
  }

  /**
   * @param \stdClass $record
   * @return RetrievalStatusResult
   */
  public static function fromStdClass(\stdClass $record): RetrievalStatusResult {
    foreach (self::REQUIRED as $req) {
      if (!property_exists($record, $req)) {
        return RetrievalStatusResult::err(new \InvalidArgumentException("Malformed retrieval status. {$req} field is missing"));
      }
    }

    if ($record->status === 500) {
      foreach (self::FAILURE_REQUIRED as $fReq) {
        if (!property_exists($record, $fReq)) {
          return RetrievalStatusResult::err(new \InvalidArgumentException("Malformed retrieval status. {$fReq} field is missing"));
        }
      }
    }

    return RetrievalStatusResult::ok(
      new RetrievalStatus(
        $record->status,
        $record->message,
        $record->UID ?? null
      )
    );
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $data = [
      'status' => $this->getStatus(),
      'message' => $this->getMessage()
    ];

    if ($this->getUID() !== null) {
      $data['UID'] = $this->getUID();
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
   * @return int
   */
  public function getStatus(): int {
    return $this->_status;
  }

  /**
   * @return string
   */
  public function getMessage(): string {
    return $this->_message;
  }

  /**
   * @return string|null
   */
  public function getUID(): ?string {
    return $this->_UID;
  }

  /**
   * @return mixed
   */
  public function jsonSerialize() {
    return $this->toArray();
  }
}