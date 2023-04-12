<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class PrioritetData implements DataClassInterface {

  public const VERY_HIGH = 1;
  public const HIGH = 2;
  public const MEDIUM = 3;
  public const LOW = 4;
  public const VERY_LOW = 5;

  public const PRIORITET = [
    1 => 'Veoma visok',
    2 => 'Visok',
    3 => 'Srednji',
    4 => 'Nizak',
    5 => 'Veoma nizak'
  ];

  public static function form(): array {
    return array_flip(self::PRIORITET);
  }

}
