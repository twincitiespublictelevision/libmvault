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

      if ($result->isError()) {
        $this->fail("PBSProfile parsing failed due to {$result->getErr()->getMessage()} for " . json_encode($sample));
      }

      $this->assertFalse($result->isError());
    }
  }
}