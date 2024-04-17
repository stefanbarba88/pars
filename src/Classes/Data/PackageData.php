<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class PackageData implements DataClassInterface {

  public const BASIC_ID = 1;
  public const CAR_ID = 2;
  public const TOOL_ID = 3;
  public const CALENDAR_ID = 4;
  public const PACKAGE = [
    1 => 'Osnovni paket',
    2 => '+ Vozila',
    3 => '+ Oprema',
    4 => '+ Kalendar',
  ];

  public static function form(): array {
    return array_flip(self::PACKAGE);
  }

}
