<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class VrstaZaposlenjaData implements DataClassInterface {

  public const VRSTA_ZAPOSLENJA = [
    1 => 'Određeno',
    2 => 'Neodređeno',
    3 => 'Privremeni i povremeni poslovi',
    4 => 'Agencija'
  ];

  public static function form(): array {
    return array_flip(self::VRSTA_ZAPOSLENJA);
  }

}
