<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;

const SCHEMA = [
  'root' => [
    'grace_period' => ['date'],
    'update_date' => ['date'],
    'first_name' => ['string', ''],
    'last_name' => ['string', ''],
    'create_date' => ['date'],
    'offer' => ['string'],
    'notes' => ['string', ''],
    'membership_id' => ['string'],
    'start_date' => ['date'],
    'status' => ['On', 'Off'],
    'token' => ['string'],
    'additional_metadata' => ['string', 'null', ''],
    'activation_date' => ['date', 'null'],
    'provisional' => ['bool'],
    'expire_date' => ['date'],
    'email' => ['string', ''],
    'current_state' => ['ref'],
    'pbs_profile' => ['ref', 'null']
  ],
  'current_state' => [
    'explanation' => ['ref'],
    'has_access' => ['bool']
  ],
  'explanation' => [
    'status' => ['On', 'Off'],
    'timing' => ['string'],
    'token_activated' => ['bool']
  ],
  'pbs_profile' => [
    'first_name' => ['string'],
    'last_name' => ['string'],
    'UID' => ['uuid'],
    'birth_date' => ['date', null],
    'email' => ['email'],
    'login_provider' => ['PBS', 'Google', 'Facebook']
  ],
  'retrieval_status' => [
    'status' => ['number'],
    'message' => ['string']
  ]
];

function generateObject(array $arr, array $schema, string $root, Generator $gen): array {
  foreach ($schema[$root] as $key => $val) {
    $k = array_keys($val)[rand(0, count($val) - 1)];
    $entry = $val[$k];

    switch ($entry) {
      case 'ref':
        $arr[$key] = generateObject([], $schema, $key, $gen);
        break;

      case 'string':
        $arr[$key] = $gen->unique()->word;
        break;

      case 'date':
        $arr[$key] = $gen->unique()->date('Y-m-d\TH:i:s\Z');
        break;

      case 'null':
        $arr[$key] = null;
        break;

      case 'number':
        $arr[$key] = $gen->unique()->numberBetween(1000, 7000);
        break;

      case 'uuid':
        $arr[$key] = $gen->unique()->uuid;
        break;

      case 'email':
        $arr[$key] = $gen->unique()->email;
        break;

      case 'bool':
        $arr[$key] = $gen->boolean;
        break;

      default:
        $arr[$key] = $entry;
        break;
    }
  }

  return $arr;
}

function generate(array $schema, string $root): \stdClass {
  return json_decode(json_encode(generateObject([], $schema, $root, Factory::create())));
}

/**
 * Class Sampler
 * @package Tests
 */
class Sampler {

  /**
   * @var array
   */
  private $_schema;

  /**
   * Sampler constructor.
   * @param array $schema
   */
  public function __construct(array $schema) {
    $this->_schema = $schema;
  }

  /**
   * @return \stdClass
   */
  public function sample($root = 'root') {
    return generate($this->_schema, $root);
  }
}