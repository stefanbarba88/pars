<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class TipTroskovaData implements DataClassInterface {

  public const TIP_TROSKOVA = [
    1 => 'Registracija',
    2 => 'Servis',
    3 => 'Održavanje',
    4 => 'Kazna',
    5 => 'Gorivo',
    6 => 'Ostalo',
  ];

  public static function form(): array {
    return array_flip(self::TIP_TROSKOVA);
  }

}
