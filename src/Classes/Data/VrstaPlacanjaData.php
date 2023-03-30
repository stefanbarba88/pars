<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class VrstaPlacanjaData implements DataClassInterface {

  public const VRSTA_PLACANJA = [
    1 => 'Besplatno',
    2 => 'Fiksna cena',
    3 => 'Plaćanje po satu',
    4 => 'Plaćanje po zadatku'
  ];

  public static function form(): array {
    return array_flip(self::VRSTA_PLACANJA);
  }

}
