<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class ZidPravacData implements DataClassInterface {

  public const HPOZ = 1;
  public const HNEG = 2;
  public const VPOZ = 3;
  public const VNEG = 4;


  public const PRAVAC = [
    1 => 'H+',
    2 => 'H-',
    3 => 'V+',
    4 => 'V-',
  ];

  public static function form(): array {
    return array_flip(self::PRAVAC);
  }

}
