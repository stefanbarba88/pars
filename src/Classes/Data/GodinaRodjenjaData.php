<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class GodinaRodjenjaData implements DataClassInterface {

  public static function data(): array {
    $years = [];
    foreach (range(1945, date('Y')) as $year) {
      $years[$year] = $year;
    }
    return $years;
  }

  public static function form(): array {
    return self::data();
  }

}
