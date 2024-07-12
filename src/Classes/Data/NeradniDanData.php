<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class NeradniDanData implements DataClassInterface {

  public const PONEDELJAK = 1;
  public const UTORAK = 2;
  public const SREDA = 3;
  public const CETVRTAK = 4;
  public const PETAK = 5;
  public const SUBOTA = 6;
  public const NEDELJA = 7;
  public const DANI = [
    1 => 'Ponedeljak',
    2 => 'Utorak',
    3 => 'Sreda',
    4 => 'Četvrtak',
    5 => 'Petak',
    6 => 'Subota',
    7 => 'Nedelja',
  ];

  public static function form(): array {
    return array_flip(self::DANI);
  }


}
