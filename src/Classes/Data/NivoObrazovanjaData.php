<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;

class NivoObrazovanjaData implements DataClassInterface {

  public const NIVO_OBRAZOVANJA = [
    0 => "I Stepen četiri razreda osnovne škole",
    1 => "II Stepen - Osnovna škola",
    2 => "III Stepen - SSS srednja škola",
    3 => "IV Stepen - SSS srednja škola",
    4 => "V Stepen - VKV - SSS srednja škola",
    5 => "VI Stepen - VŠS viša škola",
    6 => "VII - 1 VSS visoka stručna sprema",
    7 => "VI-1 Osnovne trogodišnje akademske studije",
    8 => "VI-1 Osnovne trogodišnje strukovne studije",
    9 => "VI-2 Specijalističke strukovne studije",
    10 => "VII-1a Osnovne četvorogodišnje akademske studije",
    11 => "VII-1a Integrisane master studije",
    12 => "VII-1b Master",
    13 => "VII-2 Magistar nauka",
    14 => "VII-2 Specijalizacija u medicini",
    15 => "VII-2 Specijalističke akademske studije",
    16 => "VIII Doktor nauka"
  ];

  public static function form(): array {
    return array_flip(self::NIVO_OBRAZOVANJA);
  }

  public static function getEducationLevel(int $index): string {
    if (array_key_exists($index, self::NIVO_OBRAZOVANJA)) {
      return self::NIVO_OBRAZOVANJA[$index];
    } else {
      return "N/A"; // ili bilo koja vrednost koja označava nepostojanje indeksa
    }
  }

}
