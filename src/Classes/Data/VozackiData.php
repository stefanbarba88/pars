<?php

namespace App\Classes\Data;


use App\Classes\Interfaces\DataClassInterface;

class VozackiData implements DataClassInterface {

  public const NOT = 1;
  public const INACTIVE = 2;

  public const ACTIVE = 3;
  public const VOZACKI = [
    1 => 'Nema dozvolu',
    2 => 'Neaktivni vozač',
    3 => 'Aktivni vozač'
  ];

  public static function form(): array {
    return array_flip(self::VOZACKI);
  }

}
