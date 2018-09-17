<?php

namespace Tests;

use LibMVault\MVaultRecord;
use PHPUnit\Framework\TestCase;

class MVaultRecordTest extends TestCase {
  const TESTS = 2500;

  public function testParsing() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('root');
      $result = MVaultRecord::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      $this->assertFalse($result->isError(), "MVaultRecord parsing failed due to {$msg} for " . json_encode($sample));
    }
  }

  public function testSerializesToValidMVaultRecord() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('root');
      $result = MVaultRecord::fromStdClass($sample);

      $serialized = json_encode($result->value());
      $deserResult = MVaultRecord::fromJSON($serialized);

      $msg = '';

      if ($deserResult->isError()) {
        $msg = $deserResult->getErr()->getMessage();
      }

      $this->assertFalse($deserResult->isError(), "Unable to deserialize from serialized MVaultRecord for {$serialized} due to {$msg}");
    }
  }
}