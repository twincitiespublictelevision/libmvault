<?php

namespace LibMVault;

use LibMVault\Result\MVaultResult;
use LibMVault\Result\Result;

/**
 * Class MVaultRecord
 * @package LibMVault
 */
class MVaultRecord implements \JsonSerializable {

  const REQUIRED = [
    'first_name', 'last_name', 'create_date', 'grace_period', 'update_date',
    'offer', 'membership_id', 'start_date', 'status', 'token',
    'provisional', 'expire_date', 'activation_date', 'notes', 'email',
    'pbs_profile', 'additional_metadata'
  ];

  const REQUIRED_DATES = [
    'grace_period', 'update_date', 'create_date',
    'start_date', 'expire_date'
  ];

  const DATEFORMAT = 'Y-m-d\TH:i:s\Z';

  /**
   * @var integer
   */
  private $_gracePeriod;

  /**
   * @var integer
   */
  private $_createDate;

  /**
   * @var integer
   */
  private $_updateDate;

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
  private $_offer;

  /**
   * @var string
   */
  private $_notes;

  /**
   * @var string
   */
  private $_membershipId;

  /**
   * @var integer
   */
  private $_startDate;

  /**
   * @var bool
   */
  private $_status;

  /**
   * @var string
   */
  private $_token;

  /**
   * @var string
   */
  private $_additionalMetadata;

  /**
   * @var integer
   */
  private $_activationDate;

  /**
   * @var bool
   */
  private $_provisional;

  /**
   * @var integer
   */
  private $_expireDate;

  /**
   * @var string
   */
  private $_email;

  /**
   * @var PBSProfile
   */
  private $_pbsProfile;

  /**
   * MVaultRecord constructor.
   * @param int $gracePeriod
   * @param int $updateDate
   * @param string $firstName
   * @param string $lastName
   * @param int $createDate
   * @param string $offer
   * @param string $membershipId
   * @param int $startDate
   * @param bool $status
   * @param string $token
   * @param bool $provisional
   * @param int $expireDate
   * @param int|null $activationDate
   * @param null|string $email
   * @param null|string $notes
   * @param string|null $additionalMetadata
   * @param null|PBSProfile $profile
   */
  private function __construct(
    int $gracePeriod, int $updateDate, string $firstName, string $lastName,
    int $createDate, string $offer, string $membershipId, int $startDate,
    bool $status, string $token, bool $provisional, int $expireDate,
    ?int $activationDate = null, ?string $email = null, ?string $notes = null,
    string $additionalMetadata = null, ?PBSProfile $profile = null
  ) {
    $this->_gracePeriod = $gracePeriod;
    $this->_updateDate = $updateDate;
    $this->_firstName = $firstName;
    $this->_lastName = $lastName;
    $this->_createDate = $createDate;
    $this->_offer = $offer;
    $this->_membershipId = $membershipId;
    $this->_startDate = $startDate;
    $this->_status = $status;
    $this->_token = $token;
    $this->_activationDate = $activationDate;
    $this->_provisional = $provisional;
    $this->_expireDate = $expireDate;
    $this->_email = $email;
    $this->_notes = $notes;
    $this->_additionalMetadata = $additionalMetadata;
    $this->_pbsProfile = $profile;
  }

  /**
   * @param string $record
   * @return MVaultResult
   */
  public static function fromJSON(string $record): MVaultResult {
    try {
      $parsed = ex_json_decode($record);
      return self::fromStdClass($parsed);
    } catch (\Exception $e) {
      return MVaultResult::err($e);
    }
  }

  /**
   * @param array $record
   * @return MVaultResult
   */
  public static function fromArray(array $record): MVaultResult {

    // Records do not get terribly large, so for simplicity we encode and then
    // decode from JSON at the cost of a little performance
    try {
      return self::fromJSON(ex_json_encode($record));
    } catch (\Exception $e) {
      return MVaultResult::err($e);
    }
  }

  /**
   * @param \stdClass $record
   * @return MVaultResult
   */
  public static function fromStdClass(\stdClass $record): MVaultResult {
    foreach (self::REQUIRED as $req) {
      if (!property_exists($record, $req)) {
        return MVaultResult::err(new \InvalidArgumentException("Malformed MVault record. {$req} field is missing."));
      }
    }

    $dates = [];

    foreach (self::REQUIRED_DATES as $dateField) {
      $d = strtotime($record->{$dateField});

      // Required date fields are not allowed to fail parsing. If one does then
      // the entire MVault record should be invalidated
      if ($d === false) {
        return MVaultResult::err(new \InvalidArgumentException("Malformed MVault record. {$req} date field is not correctly formatted."));
      }

      $dates[$dateField] = $d;
    }

    $activation = null;

    // Activation date is optional, but if it is present it is NOT OPTIONAL
    // that parsing of the date succeeds. If it does fail, then the record needs
    // to be invalidated
    if (isset($record->activation_date) && $record->activation_date) {
      $activation = strtotime($record->activation_date);

      if ($activation === false) {
        return MVaultResult::err(new \InvalidArgumentException("Malformed MVault record. Activation date field is not correctly formatted."));
      }
    }

    $profile = null;

    // Like activation dates, the PBS profile is allowed to be missing. But if
    // it exists then it must parse to a valid PBS Profile object
    if (isset($record->pbs_profile) && $record->pbs_profile) {
      $profileRes = PBSProfile::fromStdClass($record->pbs_profile);

      if ($profileRes->isError()) {
        return MVaultResult::err($profileRes->getErr());
      }

      $profile = $profileRes->value();
    }

    return MVaultResult::ok(
      new MVaultRecord(
        $dates['grace_period'],
        $dates['update_date'],
        $record->first_name,
        $record->last_name,
        $dates['create_date'],
        $record->offer,
        $record->membership_id,
        $dates['start_date'],
        $record->status === "On",
        $record->token,
        $record->provisional,
        $dates['expire_date'],
        $activation,
        $record->email ?? null,
        $record->notes ?? null,
        $record->additional_metadata ?? null,
        $profile
      )
    );
  }

  /**
   * @return array
   */
  public function toArray(): array {
    return [
      'grace_period' => gmdate(self::DATEFORMAT, $this->getGracePeriod()),
      'update_date' => gmdate(self::DATEFORMAT, $this->getUpdateDate()),
      'first_name' => $this->getFirstName(),
      'last_name' => $this->getLastName(),
      'create_date' => gmdate(self::DATEFORMAT, $this->getCreateDate()),
      'offer' => $this->getOffer(),
      'membership_id' => $this->getMembershipId(),
      'start_date' => gmdate(self::DATEFORMAT, $this->getStartDate()),
      'status' => $this->isStatusOn() ? "On" : "Off",
      'token' => $this->getToken(),
      'provisional' => $this->isProvisional(),
      'expire_date' => gmdate(self::DATEFORMAT, $this->getExpireDate()),
      'activation_date' => $this->getActivationDate() ? gmdate(self::DATEFORMAT, $this->getActivationDate()) : null,
      'email' => $this->getEmail(),
      'notes' => $this->getNotes(),
      'additional_metadata' => $this->getAdditionalMetadata(),
      'pbs_profile' => $this->getPBSProfile() ? $this->getPBSProfile()->toArray() : null
    ];
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
  public function getGracePeriod(): int {
    return $this->_gracePeriod;
  }

  /**
   * @return int
   */
  public function getCreateDate(): int {
    return $this->_createDate;
  }

  /**
   * @return int
   */
  public function getUpdateDate(): int {
    return $this->_updateDate;
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
  public function getOffer(): string {
    return $this->_offer;
  }

  /**
   * @return string
   */
  public function getNotes(): string {
    return $this->_notes;
  }

  /**
   * @return string
   */
  public function getMembershipId(): string {
    return $this->_membershipId;
  }

  /**
   * @return int
   */
  public function getStartDate(): int {
    return $this->_startDate;
  }

  /**
   * @return bool
   */
  public function isStatusOn(): bool {
    return $this->_status;
  }

  /**
   * @return string
   */
  public function getToken(): string {
    return $this->_token;
  }

  /**
   * @return string
   */
  public function getAdditionalMetadata(): string {
    return $this->_additionalMetadata;
  }

  /**
   * @return int|null
   */
  public function getActivationDate(): ?int {
    return $this->_activationDate;
  }

  /**
   * @return bool
   */
  public function isProvisional(): bool {
    return $this->_provisional;
  }

  /**
   * @return int
   */
  public function getExpireDate(): int {
    return $this->_expireDate;
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
  public function getPBSProfile(): ?PBSProfile {
    return $this->_pbsProfile;
  }

  /**
   * @return bool
   */
  public function isActivated(): bool {
    return $this->getPBSProfile() !== null;
  }

  /**
   * @return mixed
   */
  public function jsonSerialize() {
    return $this->toArray();
  }
}