<?php
/**
 * Created by PhpStorm.
 * User: flylord
 * Date: 23.2.19.
 * Time: 21.41
 */

namespace App\Twig;

use App\Classes\Data\UserRolesData;
use App\Entity\User;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension {

  public function getName(): string {
    return 'app_extension';
  }

  public function getFilters(): array {
    return [
      new TwigFilter('userRoleName', [$this, 'userRoleName']),
      new TwigFilter('errorsList', [$this, 'errorsList']),
    ];
  }


  public function getFunctions(): array {
    return [
    ];
  }

  public function userRoleName(User $user): string {
    return UserRolesData::userRoleTitle($user);
  }

  public function errorsList(string $error): string {

    $error = str_replace("<ul><li>"," ",$error);
    $error = str_replace("</li></ul>"," ",$error);

    return $error;
  }

}
