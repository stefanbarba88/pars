<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;
use App\Entity\User;

class CalendarColorsData implements DataClassInterface {

  public const DAN = 1;
  public const ODMOR = 2;
  public const BOLOVANJE = 3;
  public const SLAVA = 4;
  public const OSTALO = 5;

  public const DATA = [
    'DAN' => [
      'id' => self::DAN,
      'color' => '#d0e4ed',
      'text' => '#00233F',
      'title' => 'Slobodan dan',
    ],
    'ODMOR' => [
      'id' => self::ODMOR,
      'color' => '#c4dfea',
      'text' => '#00233F',
      'title' => 'Odmor',
    ],
    'BOLOVANJE' => [
      'id' => self::BOLOVANJE,
      'color' => '#3d576d',
      'text' => '#FFF',
      'title' => 'Bolovanje',
    ],
    'SLAVA' => [
      'id' => self::SLAVA,
      'color' => '#00233f',
      'text' => '#FFF',
      'title' => 'Slava',
    ],
    'OSTALO' => [
      'id' => self::OSTALO,
      'color' => '#b4ff00',
      'text' => '#00233f',
      'title' => 'Nema merenje',
    ],

  ];

  public static function form(): array {
    return self::DATA;
  }

//  public static function getColorByType(?int $type): string {
//    $defaultType = 5; // Podrazumevani ID ako je $type null
//    $type = $type ?? $defaultType; // Ako je null, koristi 5
//
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($type) {
//        return $data['id'] == $type;
//      });
//
//    return $d['color'] ?? ''; // Osiguravamo da ne bude greška ako ne nađe podatak
//  }
//  public static function getTitleByType(int $type): string {
//
//
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($type) {
//        return $data['id'] == $type;
//      });
//
//    return $d['title'];
//  }
//  public static function getTextByType(int $type): string {
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($type) {
//        return $data['id'] == $type;
//      });
//
//    return $d['text'];
//  }

  public static function getColorByType(?int $type): string {
    $type = $type ?? 5; // Ako je null, koristi 5

    $d = Map::from(self::DATA)
      ->find(fn(array $data) => $data['id'] == $type);

    return $d['color'] ?? ''; // Ako nije pronađen, vraća prazan string
  }

  public static function getTitleByType(?int $type): string {
    $type = $type ?? 5; // Ako je null, koristi 5

    $d = Map::from(self::DATA)
      ->find(fn(array $data) => $data['id'] == $type);

    return $d['title'] ?? ''; // Ako nije pronađen, vraća prazan string
  }

  public static function getTextByType(?int $type): string {
    $type = $type ?? 5; // Ako je null, koristi 5

    $d = Map::from(self::DATA)
      ->find(fn(array $data) => $data['id'] == $type);

    return $d['text'] ?? ''; // Ako nije pronađen, vraća prazan string
  }

//  public static function formForForm(): array {
////        $data = ['' => ''];
//    $data = [];
//        foreach (self::DATA as $v) {
//            $data[$v['title']] = $v['id'];
//        }
//
//        return $data;
//    }
//
//  public static function formByType(int $predefinedType = 0): array {
//    $data = ['' => ''];
//    if ($predefinedType > 0) {
//      $data = [];
//      foreach (self::DATA as $v) {
//        if ($predefinedType == $v['id']) {
//          $data[$v['title']] = $v['role'];
//        }
//      }
//    } else {
//      foreach (self::DATA as $v) {
//        $data[$v['title']] = $v['role'];
//      }
//    }
//
//    return $data;
//  }
//
//
//
//  public static function userRoleTitle(User $user): string {
//    $roles = $user->getRoles();
//    $title = '';
//    foreach ($roles as $role) {
//      if (isset(self::DATA[$role])) {
//        $title = self::DATA[$role]['title'];
//      }
//    }
//
//    return $title;
//  }
//
//  public static function userRole(User $user): string {
//    $roles = $user->getRoles();
//    $data = '';
//    foreach ($roles as $role) {
//      if (isset(self::DATA[$role])) {
//        $data = $role;
//      }
//    }
//
//    return $data;
//  }
//
//  public static function getRoleByType(int $type): string {
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($type) {
//        return $data['id'] == $type;
//      });
//
//    return $d['role'];
//  }
//
//  public static function getTitleByType(int $type): string {
//      $d = Map::from(self::DATA)
//          ->find(function (array $data) use ($type) {
//              return $data['id'] == $type;
//          });
//
//      return $d['title'];
//  }
//
//  public static function getTypeByRole(string $role): int {
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($role) {
//        return $data['role'] == $role;
//      });
//
//    return $d['id'];
//  }
//
//  public static function getBadgeByType(int $type): string {
//    $d = Map::from(self::DATA)
//      ->find(function (array $data) use ($type) {
//        return $data['id'] == $type;
//      });
//
//    return $d['badge'];
//  }

}
