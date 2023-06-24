<?php
declare(strict_types=1);

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class CalendarData implements DataClassInterface {

  public const TIP = [
    1 => 'Slobodan dan',
    2 => 'Odmor',
    3 => 'Bolovanje',
    4 => 'Slava'
  ];

  public static function form(): array {
    return array_flip(self::TIP);
  }

}
