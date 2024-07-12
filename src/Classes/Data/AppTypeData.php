<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class AppTypeData implements DataClassInterface {

  public const PARS = 0;
  public const KADROVSKA = 1;
  public const TIPOVI = [
    0 => 'Pars aplikacija',
    1 => 'Kadrovska aplikacija'
  ];

  public static function form(): array {
    return array_flip(self::TIPOVI);
  }

}
