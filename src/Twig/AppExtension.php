<?php
/**
 * Created by PhpStorm.
 * User: flylord
 * Date: 23.2.19.
 * Time: 21.41
 */

namespace App\Twig;

use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Doctrine\ORM\EntityManagerInterface;

class AppExtension extends AbstractExtension {

  private EntityManagerInterface $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

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
      new TwigFunction('getLogStatus', [$this, 'getLogStatus']),
      new TwigFunction('getTaskStatus', [$this, 'getTaskStatus']),
      new TwigFunction('getLogStatusByUser', [$this, 'getLogStatusByUser']),
      new TwigFunction('getDostupnostByUser', [$this, 'getDostupnostByUser']),
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

  public function getLogStatus(Task $task): array{
    return $this->entityManager->getRepository(TaskLog::class)->getLogStatus($task);
  }

  public function getLogStatusByUser(Task $task, User $user): int{
    return $this->entityManager->getRepository(TaskLog::class)->getLogStatusByUser($task, $user);
  }

  public function getTaskStatus(Task $task): int{
    return $this->entityManager->getRepository(Task::class)->taskStatus($task);
  }

  public function getDostupnostByUser(User $user): ?int {
    return $this->entityManager->getRepository(Availability::class)->getDostupnostByUserTwig($user);
  }

}
