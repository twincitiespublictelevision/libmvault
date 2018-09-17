<?php

namespace Tests;

use LibMVault\RetrievalStatus;
use PHPUnit\Framework\TestCase;

class RetrievalStatusTest extends TestCase {
  const TESTS = 2500;

  public function testParsingSuccess() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('retrieval_success');
      $result = RetrievalStatus::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      $this->assertFalse($result->isError(), "RetrievalStatus parsing failed due to {$msg} for " . json_encode($sample));
    }
  }

  public function testSerializesSuccessToValidRetrievalStatus() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('retrieval_success');
      $result = RetrievalStatus::fromStdClass($sample);

      $serialized = json_encode($result->value());
      $deserResult = RetrievalStatus::fromJSON($serialized);

      $msg = '';

      if ($deserResult->isError()) {
        $msg = $deserResult->getErr()->getMessage();
      }

      $this->assertFalse($deserResult->isError(), "Unable to deserialize from serialized RetrievalStatus for {$serialized} due to {$msg}");
    }
  }

  public function testParsingFailure() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('retrieval_success');
      $result = RetrievalStatus::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      $this->assertFalse($result->isError(), "RetrievalStatus parsing failed due to {$msg} for " . json_encode($sample));
    }
  }

  public function testSerializesFailureToValidRetrievalStatus() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('retrieval_success');
      $result = RetrievalStatus::fromStdClass($sample);

      $serialized = json_encode($result->value());
      $deserResult = RetrievalStatus::fromJSON($serialized);

      $msg = '';

      if ($deserResult->isError()) {
        $msg = $deserResult->getErr()->getMessage();
      }

      $this->assertFalse($deserResult->isError(), "Unable to deserialize from serialized RetrievalStatus for {$serialized} due to {$msg}");
    }
  }
}