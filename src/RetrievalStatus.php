<?php

namespace LibMVault;

use LibMVault\Result\RetrievalStatusResult;

/**
 * Class RetrievalStatus
 *
 * Represents the retrieval status of the PBS Profile sub-request of an
 * activated MVault record. Contains the status code and message provided by the
 * PBS API.
 *
 * @package LibMVault
 */
class RetrievalStatus implements \JsonSerializable {

  /**
   * @var array
   */
  const REQUIRED = [
    'status', 'message'
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
   * RetrievalStatus constructor.
   * @param int $status
   * @param string $message
   */
  private function __construct(int $status, string $message) {
    $this->_status = $status;
    $this->_message = $message;
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

    return RetrievalStatusResult::ok(
      new RetrievalStatus(
        $record->status,
        $record->message
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

    return $data;
  }

  /**
   * @return \stdClass
   */
  public function toStdClass(): \stdClass {
    return json_decode(json_encode($this));
  }

  /**
   * Gets the status code for the retrieval status
   *
   * @return int
   */
  public function getStatus(): int {
    return $this->_status;
  }

  /**
   * Gets the message describing the retrieval status
   *
   * @return string
   */
  public function getMessage(): string {
    return $this->_message;
  }

  /**
   * @return mixed
   */
  public function jsonSerialize() {
    return $this->toArray();
  }
}