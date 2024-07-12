<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class AddonData implements DataClassInterface {

  public const LEKARSKI = 1;
  public const EVALUACIJA = 2;
  public const EDUKACIJA = 3;
  public const STIMULACIJA = 4;
  public const DESTIMULACIJA = 5;

  public const ADDON = [
    1 => 'Lekarski pregled',
    2 => 'Evaluacija',
    3 => 'Edukacija',
    4 => 'Stimulacija',
    5 => 'Destimulacija',
  ];

  public static function form(): array {
    return array_flip(self::ADDON);
  }

}
