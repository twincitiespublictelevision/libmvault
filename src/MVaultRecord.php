<?php

namespace LibMVault;

use LibMVault\Result\MVaultResult;

/**
 * Class MVaultRecord
 *
 * Represents a response from the MVault API. Required fields should mirror the
 * fields that exist in an MVault response. These fields only need be present in
 * the value passed in for parsing, they do not need to have actual values and
 * may be null.
 *
 * Date fields must be able to be parsed. If a date field fails to parse then
 * the entire record is considered invalid. There is one caveat to this rule for
 * the activation_date field. The activation date is also allowed to be null,
 * in which case it is a valid date field that can not be parsed. If the
 * activation date contains a truthy value though, then it must be able to be
 * parsed.
 *
 * @package LibMVault
 */
class MVaultRecord implements \JsonSerializable {

  /**
   * @var array
   */
  const REQUIRED = [
    'first_name', 'last_name', 'create_date', 'grace_period', 'update_date',
    'offer', 'membership_id', 'start_date', 'status', 'token',
    'provisional', 'expire_date', 'activation_date', 'notes', 'email',
    'pbs_profile', 'additional_metadata'
  ];

  /**
   * @var array
   */
  const REQUIRED_DATES = [
    'grace_period', 'update_date', 'create_date',
    'start_date', 'expire_date'
  ];

  /**
   * @var string
   */
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
   * @var string|null
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
   * @var string|null
   */
  private $_additionalMetadata;

  /**
   * @var int|null
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
   * @var string|null
   */
  private $_email;

  /**
   * @var PBSProfile|null
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
    int $createDate, ?string $offer, string $membershipId, int $startDate,
    bool $status, string $token, bool $provisional, int $expireDate,
    ?int $activationDate = null, ?string $email = null, ?string $notes = null,
    ?string $additionalMetadata = null, ?PBSProfile $profile = null
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
        return MVaultResult::err(new \InvalidArgumentException("Malformed MVault record. {$dateField} date field is not correctly formatted."));
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
   * Gets an integer representation of the grace period end date.
   *
   * @return int
   */
  public function getGracePeriod(): int {
    return $this->_gracePeriod;
  }

  /**
   * Gets an integer representation of the record creation date.
   *
   * @return int
   */
  public function getCreateDate(): int {
    return $this->_createDate;
  }

  /**
   * Gets an integer representation of the record last updated date.
   *
   * @return int
   */
  public function getUpdateDate(): int {
    return $this->_updateDate;
  }

  /**
   * Gets the first name stored in the Mvault record.
   *
   * @return string
   */
  public function getFirstName(): string {
    return $this->_firstName;
  }

  /**
   * Gets the last name stored in the Mvault record.
   *
   * @return string
   */
  public function getLastName(): string {
    return $this->_lastName;
  }

  /**
   * Gets the offer assigned to the MVault record.
   *
   * @return string
   */
  public function getOffer(): ?string {
    return $this->_offer;
  }

  /**
   * Gets the optional notes field of the MVault record.
   *
   * @return string|null
   */
  public function getNotes(): ?string {
    return $this->_notes;
  }

  /**
   * Gets the membership id of the MVault record.
   *
   * @return string
   */
  public function getMembershipId(): string {
    return $this->_membershipId;
  }

  /**
   * Gets an integer representation of the record start date
   *
   * @return int
   */
  public function getStartDate(): int {
    return $this->_startDate;
  }

  /**
   * Gets the boolean status of the Status toggle of the MVault record.
   *
   * @return bool
   */
  public function isStatusOn(): bool {
    return $this->_status;
  }

  /**
   * Gets the token string of the MVault record.
   *
   * @return string
   */
  public function getToken(): string {
    return $this->_token;
  }

  /**
   * Gets the optional additional metadata field of the MVault record.
   *
   * @return string|null
   */
  public function getAdditionalMetadata(): ?string {
    return $this->_additionalMetadata;
  }

  /**
   * Gets an integer representation of the activation start date. This value may
   * be null, representing that the record has not been activated.
   *
   * @return int|null
   */
  public function getActivationDate(): ?int {
    return $this->_activationDate;
  }

  /**
   * Gets the boolean status of the Provisional toggle of the MVault record.
   *
   * @return bool
   */
  public function isProvisional(): bool {
    return $this->_provisional;
  }

  /**
   * Gets an integer representation of the record expiration date
   *
   * @return int
   */
  public function getExpireDate(): int {
    return $this->_expireDate;
  }

  /**
   * Gets the email stored in the Mvault record.
   *
   * @return string|null
   */
  public function getEmail(): ?string {
    return $this->_email;
  }

  /**
   * Gets the object representing the PBS profile sub-request that is made for
   * activate MVault records. This may return null in the case that the MVault
   * record has not yet been activated.
   *
   * @return PBSProfile|null
   */
  public function getPBSProfile(): ?PBSProfile {
    return $this->_pbsProfile;
  }

  /**
   * Gets the boolean activation of the MVault record.
   *
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