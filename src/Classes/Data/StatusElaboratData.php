<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class StatusElaboratData implements DataClassInterface {

  public const KREIRAN = 1;
  public const NEKOMPLETNO = 2;
  public const KOMPLETNO = 3;
  public const ZATVOREN = 4;

  public const STATUS = [
    1 => 'Nema dokumentacije',
    2 => 'Nekompletna / neproverena',
    3 => 'Kompletna i proverena',
    4 => 'Zatvoreno',
  ];

  public static function form(): array {
    return array_flip(self::STATUS);
  }

}
