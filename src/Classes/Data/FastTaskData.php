<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class FastTaskData implements DataClassInterface {

  public const OPEN = 1;
  public const SAVED = 2;
  public const EDIT = 3;
  public const FINAL = 4;

  public const STATUS = [
    1 => 'Otvoren',
    2 => 'Sačuvan',
    3 => 'Izmenjen',
    4 => 'Konačan',
  ];

  public static function form(): array {
    return array_flip(self::STATUS);
  }

}
