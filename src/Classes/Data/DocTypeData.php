<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class DocTypeData implements DataClassInterface {

  public const LK = 1;
  public const DOC = 2;
//  public const EDUKACIJA = 3;
//  public const STIMULACIJA = 4;
//  public const DESTIMULACIJA = 5;

  public const TIP = [
    1 => 'Lekarski pregled',
    2 => 'Dokumenta',
//    3 => 'Edukacija',
//    4 => 'Stimulacija',
//    5 => 'Destimulacija',
  ];

  public static function form(): array {
    return array_flip(self::TIP);
  }

}
