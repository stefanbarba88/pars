<?php

namespace App\Classes\Data;


use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;

class TipProjektaData implements DataClassInterface {

  public const FIKSNO = 1;
  public const LETECE = 2;
  public const KOMBINOVANO = 3;
  public const TIP = [
    1 => 'Fiksni',
    2 => 'Leteći',
    3 => 'Kombinovano'
  ];

  public static function form(): array {
    return array_flip(self::TIP);
  }

  public static function getBadgeByType(int $type): string {
    $d = Map::from(self::TIP)
      ->find(function (array $data) use ($type) {
        return $data['id'] == $type;
      });

    return $d['badge'];
  }

}
