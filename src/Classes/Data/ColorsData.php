<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;
use App\Entity\User;

class ColorsData implements DataClassInterface {

  public const DATA = [
    ['class' =>'text-bg-primary', 'value' => 'primary', 'option' => 'Primary'],
    ['class' =>'text-bg-secondary', 'value' => 'secondary', 'option' => 'Secondary'],
    ['class' =>'text-bg-danger', 'value' => 'danger', 'option' => 'Danger'],
    ['class' =>'text-bg-warning', 'value' => 'warning', 'option' => 'Warning'],
    ['class' =>'text-bg-success', 'value' => 'success', 'option' => 'Success'],
    ['class' =>'text-bg-info', 'value' => 'info', 'option' => 'Info'],
    ['class' =>'text-bg-dark', 'value' => 'dark', 'option' => 'Dark'],
    ['class' =>'text-bg-pink', 'value' => 'pink', 'option' => 'Pink'],
    ['class' =>'text-bg-purple', 'value' => 'purple', 'option' => 'Purple'],
    ['class' =>'text-bg-indigo', 'value' => 'indigo', 'option' => 'Indigo'],
    ['class' =>'text-bg-yellow', 'value' => 'yellow', 'option' => 'Yellow'],

  ];

  public static function form(): array {
    return self::DATA;
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
