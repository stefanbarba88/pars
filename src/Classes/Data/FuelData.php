<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class FuelData implements DataClassInterface {

  public const PRAZNO = 1;
  public const CETVRT = 2;
  public const POLA = 3;
  public const TRI_CETVRTI = 4;
  public const PUN = 5;

  public const STATUS = [
    1 => 'Prazan rezervoar',
    2 => '1/4 rezervoara',
    3 => '1/2 rezervoara',
    4 => '3/4 razervoara',
    5 => 'Pun rezervoar',
  ];

  public static function form(): array {
    return array_flip(self::STATUS);
  }

}
