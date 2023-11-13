<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class AvailabilityData implements DataClassInterface {

  public const NEDOSTUPAN = 1;
  public const IZASAO = 2;
  public const PRISUTAN = 3;
  public const DOSTUPNOST = [
    1 => '1 - Nedostupan',
    2 => '2 - Izašao',
    3 => '3 - Prisutan',
  ];

  public static function form(): array {
    return array_flip(self::DOSTUPNOST);
  }

}
