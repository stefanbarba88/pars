<?php

namespace App\Classes\Data;


use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;

class TipProstoraData implements DataClassInterface {

  public const STAMBENI = 1;
  public const POSLOVNI = 2;
  public const GARAZNI = 3;
  public const TIP = [
    1 => 'Stambeni',
    2 => 'Poslovni',
    3 => 'Garažni'
  ];

  public static function form(): array {
    return array_flip(self::TIP);
  }



}
