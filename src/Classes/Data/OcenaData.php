<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class OcenaData implements DataClassInterface {

  public const OCENA = [
    1 => '1',
    2 => '2',
    3 => '3',
    4 => '4',
    5 => '5',
    6 => '6',
    7 => '7',
    8 => '8',
    9 => '9',
    10 => '10',
  ];

  public static function form(): array {
    return array_flip(self::OCENA);
  }

}
