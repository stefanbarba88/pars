<?php

namespace App\Classes\Data;


use App\Classes\Interfaces\DataClassInterface;

class PolData implements DataClassInterface {

  public const POL = [
    1 => 'Muški',
    2 => 'Ženski'
  ];

  public static function form(): array {
    return array_flip(self::POL);
  }

}
