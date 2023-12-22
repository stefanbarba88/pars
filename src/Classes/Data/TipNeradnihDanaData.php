<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class TipNeradnihDanaData implements DataClassInterface {

  public const PRAZNIK = 1;
  public const KOLEKTIVNI_ODMOR = 2;
  public const NEDELJA = 3;
  public const NEDELJA_ODMOR = 4;
  public const NEDELJA_PRAZNIK = 5;



  public const TIP_NERADNIH_DANA = [
    1 => 'Praznik',
    2 => 'Kolektivni odmor',
  ];

  public static function form(): array {
    return array_flip(self::TIP_NERADNIH_DANA);
  }

}
