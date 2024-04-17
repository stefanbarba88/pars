<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class ExportData implements DataClassInterface {

  public const BASIC = 1;
  public const EXCEL = 2;
  public const PDF = 3;
  public const EXPORT = [
    1 => 'Pregled',
    2 => 'Excel',
    3 => 'PDF',
  ];

  public static function form(): array {
    return array_flip(self::EXPORT);
  }

}
