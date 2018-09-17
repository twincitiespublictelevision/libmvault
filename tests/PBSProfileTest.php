<?php

namespace Tests;

use LibMVault\PBSProfile;
use PHPUnit\Framework\TestCase;

class PBSProfileTest extends TestCase {
  const TESTS = 2500;

  public function testParsingSuccess() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('pbs_profile_success');
      $result = PBSProfile::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      $this->assertFalse($result->isError(), "PBSProfile parsing failed due to {$msg} for " . json_encode($sample));
    }
  }

  public function testSerializesSuccessToValidPBSProfile() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('pbs_profile_success');
      $result = PBSProfile::fromStdClass($sample);

      $serialized = json_encode($result->value());
      $deserResult = PBSProfile::fromJSON($serialized);

      $msg = '';

      if ($deserResult->isError()) {
        $msg = $deserResult->getErr()->getMessage();
      }

      $this->assertFalse($deserResult->isError(), "Unable to deserialize from serialized PBSProfile for {$serialized} due to {$msg}");
    }
  }

  public function testParsingFailure() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('pbs_profile_failure');
      $result = PBSProfile::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      $this->assertFalse($result->isError(), "PBSProfile parsing failed due to {$msg} for " . json_encode($sample));
    }
  }

  public function testSerializesFailureToValidPBSProfile() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('pbs_profile_failure');
      $result = PBSProfile::fromStdClass($sample);

      $serialized = json_encode($result->value());
      $deserResult = PBSProfile::fromJSON($serialized);

      $msg = '';

      if ($deserResult->isError()) {
        $msg = $deserResult->getErr()->getMessage();
      }

      $this->assertFalse($deserResult->isError(), "Unable to deserialize from serialized PBSProfile for {$serialized} due to {$msg}");
    }
  }
}