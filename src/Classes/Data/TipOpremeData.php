<?php

namespace App\Classes\Data;


use App\Classes\Interfaces\DataClassInterface;

class TipOpremeData implements DataClassInterface {

  public const LAPTOP = 1;
  public const TELEFON = 2;

  public const TS = 3;
  public const LS = 4;
  public const GPS = 5;
  public const DRON = 6;
  public const SKENER = 7;
  public const TIP = [
    1 => 'Laptop',
    2 => 'Mobilni telefon',
    3 => 'TS',
    4 => 'LS',
    5 => 'GPS',
    6 => 'Dron',
    7 => 'Skener'
  ];

  public static function form(): array {
    return array_flip(self::TIP);
  }

}
