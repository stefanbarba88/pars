<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class CleanData implements DataClassInterface {

  public const LOSE = 1;
  public const DOVOLJNO = 2;
  public const DOBRO = 3;
  public const VRLO_DOBRO = 4;
  public const ODLICNO = 5;

  public const OCENA = [
    1 => '1 - Loše',
    2 => '2 - Dovoljno',
    3 => '3 - Dobro',
    4 => '4 - Vrlo dobro',
    5 => '5 - Odlično',
  ];

  public static function form(): array {
    return array_flip(self::OCENA);
  }

}
