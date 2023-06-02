<?php

namespace App\Classes\Data;

use Aimeos\Map;
use App\Classes\Interfaces\DataClassInterface;
use App\Entity\User;

class UserRolesData implements DataClassInterface {

  public const ROLE_SUPER_ADMIN = 1;
  public const ROLE_ADMIN = 2;
  public const ROLE_MANAGER = 3;
  public const ROLE_EMPLOYEE = 4;
  public const ROLE_CLIENT = 5;


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
    'ROLE_MANAGER' => [
      'id' => self::ROLE_MANAGER,
      'title' => 'Menadžment',
      'role' => 'ROLE_MANAGER',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-warning">Menadžment</span>',
    ],
    'ROLE_EMPLOYEE' => [
      'id' => self::ROLE_EMPLOYEE,
      'title' => 'Zaposleni',
      'role' => 'ROLE_EMPLOYEE',
      'badge' => '<span class="badge bg-light border-start border-width-3 text-body rounded-start-0 border-info">Zaposleni</span>',
    ],
    'ROLE_CLIENT' => [
      'id' => self::ROLE_CLIENT,
      'title' => 'Klijent',
      'role' => 'ROLE_CLIENT',
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

  public static function formForFormByUserRole(int $userType): array {
    $data = [];
    foreach (self::DATA as $v) {
      switch ($userType) {
        case UserRolesData::ROLE_SUPER_ADMIN:
          $data[$v['title']] = $v['id'];
          break;
        case UserRolesData::ROLE_ADMIN:
          if ($v['id'] != self::ROLE_SUPER_ADMIN && $v['id'] != self::ROLE_ADMIN ) {
            $data[$v['title']] = $v['id'];
          }
          break;
        default:
        case UserRolesData::ROLE_MANAGER:
          if ($v['id'] != self::ROLE_SUPER_ADMIN && $v['id'] != self::ROLE_ADMIN && $v['id'] != self::ROLE_MANAGER ) {
            $data[$v['title']] = $v['id'];
          }
      }
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
