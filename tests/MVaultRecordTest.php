<?php

namespace Tests;

use LibMVault\MVaultRecord;
use PHPUnit\Framework\TestCase;

class MVaultRecordTest extends TestCase {
  const TESTS = 9999;

  public function testParsing() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('root');
      $result = MVaultRecord::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      if ($sample->pbs_profile === null || $sample->pbs_profile->retrieval_status->status === 200) {
        $this->assertFalse($result->isError(), "MVaultRecord parsing failed due to {$msg} for " . json_encode($sample));
      } else {
        $this->assertTrue($result->isError(), "MVaultRecord parsing failed due to {$msg} for " . json_encode($sample));
      }
    }
  }
}