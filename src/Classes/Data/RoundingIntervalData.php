<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class RoundingIntervalData implements DataClassInterface {

  public const MIN_5 = 5;
  public const MIN_10 = 10;
  public const MIN_15 = 15;

  public const ROUNDING_INTERVAL = [
    5 => '5 minuta',
    10 => '10 minuta',
    15 => '15 minuta',
  ];

  public static function form(): array {
    return array_flip(self::ROUNDING_INTERVAL);
  }

}
