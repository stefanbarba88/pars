<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class RepeatingIntervalData implements DataClassInterface {

  public const DAY = 1;

  public const WEEK = 2;

  public const MONTH = 3;

  public const YEAR = 4;
  public const TACAN_DATUM = 5;

  public const REPEATING_INTERVAL = [
    1 => 'Dnevno',
    2 => 'Sedmično',
    3 => 'Mesečno',
    4 => 'Godišnje',
    5 => 'Tačan datum',
  ];

  public static function form(): array {
    return array_flip(self::REPEATING_INTERVAL);
  }

}
