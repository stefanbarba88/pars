<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class WorkWeekData implements DataClassInterface {

  public const FOUR_DAYS = 5;
  public const FIVE_DAYS = 6;
  public const SIX_DAYS = 7;
  public const SEVEN_DAYS = 8;
  public const WORKWEEK = [
    5 => '4 dana',
    6 => '5 dana',
    7 => '6 dana',
    8 => '7 dana',
  ];

  public static function form(): array {
    return array_flip(self::WORKWEEK);
  }

}
