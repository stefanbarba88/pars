<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class VrstaPlacanjaData implements DataClassInterface {

  public const BESPLATNO = 1;
  public const FIKSNA_CENA = 2;
  public const PLACANJE_PO_SATU = 3;
  public const PLACANJE_PO_ZADATKU = 4;
  public const PLACANJE_PO_DANU = 5;
  public const PLACANJE_PO_MESECU = 6;

  public const VRSTA_PLACANJA = [
    1 => 'Besplatno',
    2 => 'Fiksna cena',
    3 => 'Plaćanje po satu',
    4 => 'Plaćanje po zadatku',
    5 => 'Plaćanje po danu',
    6 => 'Plaćanje po mesecu'
  ];

  public static function form(): array {
    return array_flip(self::VRSTA_PLACANJA);
  }

}
