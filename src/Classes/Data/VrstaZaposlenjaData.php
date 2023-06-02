<?php

namespace App\Classes\Data;

use App\Classes\Interfaces\DataClassInterface;

class VrstaZaposlenjaData implements DataClassInterface {

  public const VRSTA_ZAPOSLENJA = [
    0 => 'Nije u radnom odnosu',
    1 => 'Neodređeno',
    2 => 'Određeno',
    3 => 'Privremeni i povremeni poslovi',
    4 => 'Agencija',
    5 => 'Omladinska zadruga'
  ];

  public static function form(): array {
    return array_flip(self::VRSTA_ZAPOSLENJA);
  }

}
