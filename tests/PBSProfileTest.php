<?php

namespace Tests;

use LibMVault\PBSProfile;
use PHPUnit\Framework\TestCase;

class PBSProfileTest extends TestCase {
  const TESTS = 9999;

  public function testParsing() {
    $s = new Sampler(SCHEMA);

    foreach (range(0, self::TESTS) as $t) {
      $sample = $s->sample('pbs_profile');
      $result = PBSProfile::fromStdClass($sample);
      $msg = $result->getErr() ? $result->getErr()->getMessage() : '';

      if ($sample->retrieval_status->status === 200) {
        $this->assertFalse($result->isError(), "PBSProfile parsing failed due to {$msg} for " . json_encode($sample));
      } else {
        $this->assertTrue($result->isError(), "PBSProfile parsing failed due to {$msg} for " . json_encode($sample));
      }
    }
  }
}