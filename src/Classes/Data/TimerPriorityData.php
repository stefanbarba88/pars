<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;
use App\Entity\User;

class TimerPriorityData implements DataClassInterface {

  public const TASK = 1;
  public const FIRST_ASSIGN = 2;
  public const ROLE_GEO = 3;
  public const ROLE_FIG = 4;

  public const DATA = [
    1 => 'Svi dnevnici zadatka',
    2 => 'Dnevnik prvog zaduženog',
    3 => 'Dnevnik geodete',
    4 => 'Dnevnik figuranta',
  ];

  public static function form(): array {
    return array_flip(self::DATA);
  }

  public static function getPriorityByType(int $type): string {
    return self::DATA[$type];
  }

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
