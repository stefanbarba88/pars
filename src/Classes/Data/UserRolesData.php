<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;
use App\Entity\User;

class UserRolesData implements DataClassInterface {

  public const ROLE_SUPER_ADMIN = 1;

  public const ROLE_ADMIN = 2;

  public const ROLE_GEODETA = 3;
  public const ROLE_FIGURANT = 4;
  public const ROLE_KLIJENT = 5;


  public const DATA = [
    'ROLE_SUPER_ADMIN' => [
      'id' => self::ROLE_SUPER_ADMIN,
      'title' => 'Super Admin',
      'role' => 'ROLE_SUPER_ADMIN',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-danger">Super Admin</span>',
    ],
    'ROLE_ADMIN' => [
      'id' => self::ROLE_ADMIN,
      'title' => 'Administrator',
      'role' => 'ROLE_ADMIN',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-success">Administrator</span>',
    ],
    'ROLE_GEODETA' => [
      'id' => self::ROLE_GEODETA,
      'title' => 'Geodeta',
      'role' => 'ROLE_GEODETA',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-warning">Geodeta</span>',
    ],
    'ROLE_POMOCNI_GEODETA' => [
      'id' => self::ROLE_FIGURANT,
      'title' => 'Figurant',
      'role' => 'ROLE_FIGURANT',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-info">Figurant</span>',
    ],
    'ROLE_KLIJENT' => [
      'id' => self::ROLE_KLIJENT,
      'title' => 'Klijent',
      'role' => 'ROLE_KLIJENT',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-secondary">Klijent</span>',
    ],
  ];

  public static function form(): array {
    return self::DATA;
  }

  public static function formForForm(): array {
//        $data = ['' => ''];
    $data = [];
        foreach (self::DATA as $v) {
            $data[$v['title']] = $v['id'];
        }

        return $data;
    }

  public static function formByType(int $predefinedType = 0): array {
    $data = ['' => ''];
    if ($predefinedType > 0) {
      $data = [];
      foreach (self::DATA as $v) {
        if ($predefinedType == $v['id']) {
          $data[$v['title']] = $v['role'];
        }
      }
    } else {
      foreach (self::DATA as $v) {
        $data[$v['title']] = $v['role'];
      }
    }

    return $data;
  }



  public static function userRoleTitle(User $user): string {
    $roles = $user->getRoles();
    $title = '';
    foreach ($roles as $role) {
      if (isset(self::DATA[$role])) {
        $title = self::DATA[$role]['title'];
      }
    }

    return $title;
  }

  public static function userRole(User $user): string {
    $roles = $user->getRoles();
    $data = '';
    foreach ($roles as $role) {
      if (isset(self::DATA[$role])) {
        $data = $role;
      }
    }

    return $data;
  }

  public static function getRoleByType(int $type): string {
    $d = Map::from(self::DATA)
      ->find(function (array $data) use ($type) {
        return $data['id'] == $type;
      });

    return $d['role'];
  }

  public static function getTitleByType(int $type): string {
      $d = Map::from(self::DATA)
          ->find(function (array $data) use ($type) {
              return $data['id'] == $type;
          });

      return $d['title'];
  }

  public static function getTypeByRole(string $role): int {
    $d = Map::from(self::DATA)
      ->find(function (array $data) use ($role) {
        return $data['role'] == $role;
      });

    return $d['id'];
  }

  public static function getBadgeByType(int $type): string {
    $d = Map::from(self::DATA)
      ->find(function (array $data) use ($type) {
        return $data['id'] == $type;
      });

    return $d['badge'];
  }

}
