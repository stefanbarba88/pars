<?php
declare(strict_types=1);

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class PotvrdaData implements DataClassInterface {

  public const POTVRDA = [
    1 => 'Da',
    0 => 'Ne'
  ];

  public static function form(): array {
    return array_flip(self::POTVRDA);
  }

}
