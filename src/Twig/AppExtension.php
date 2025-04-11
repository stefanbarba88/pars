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
use App\Entity\Car;
use App\Entity\ManagerChecklist;
use App\Entity\Phase;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\TimeTask;
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
      new TwigFunction('getDostupnostByUserSutra', [$this, 'getDostupnostByUserSutra']),
      new TwigFunction('getDostupnostByUser', [$this, 'getDostupnostByUser']),
      new TwigFunction('getCountTasksByProject', [$this, 'getCountTasksByProject']),
      new TwigFunction('getCountTasksByProjectIntern', [$this, 'getCountTasksByProjectIntern']),
      new TwigFunction('getCountDaysTasksByProject', [$this, 'getCountDaysTasksByProject']),
      new TwigFunction('getTekuciPoslovi', [$this, 'getTekuciPoslovi']),
      new TwigFunction('getCarById', [$this, 'getCarById']),
      new TwigFunction('getUserById', [$this, 'getUserById']),
      new TwigFunction('getDaysRemainingProject', [$this, 'getDaysRemainingProject']),
      new TwigFunction('getDaysRemaining', [$this, 'getDaysRemaining']),
      new TwigFunction('getDaysRemainingCar', [$this, 'getDaysRemainingCar']),
      new TwigFunction('getDaysRemainingTask', [$this, 'getDaysRemainingTask']),
      new TwigFunction('getDaysRemainingChecklist', [$this, 'getDaysRemainingChecklist']),
      new TwigFunction('getDaysRemainingPhase', [$this, 'getDaysRemainingPhase']),
      new TwigFunction('getUcinkovitost1', [$this, 'getUcinkovitost1']),
      new TwigFunction('getUcinkovitost2', [$this, 'getUcinkovitost2']),
      new TwigFunction('getUcinkovitostProject', [$this, 'getUcinkovitostProject']),
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

  public function getTekuciPoslovi(User $user): bool {
    $task = $this->entityManager->getRepository(TimeTask::class)->findOneBy(['user' => $user, 'finish' => null]);
    if (is_null($task)) {
      return false;
    }
    return true;
  }

  public function getDostupnostByUserSutra(User $user): ?int {
    return $this->entityManager->getRepository(Availability::class)->getDostupnostByUserTwigSutra($user);
  }

  public function getCountTasksByProject(Project $project):array {
    return $this->entityManager->getRepository(Project::class)->getCountTasksByProject($project);
  }

  public function getCountTasksByProjectIntern(Project $project):array {
    return $this->entityManager->getRepository(Project::class)->getCountTasksByProjectIntern($project);
  }

  public function getCountDaysTasksByProject(Project $project):array {
    return $this->entityManager->getRepository(Project::class)->getCountDaysTasksByProject($project);
  }

  public function getCarById(int $id): string {
    $car = $this->entityManager->getRepository(Car::class)->find($id);
    return $car->getCarName();
  }

  public function getUserById(int $id): string {
    $user = $this->entityManager->getRepository(User::class)->find($id);
    return $user->getFullName();
  }
  public function getDaysRemainingProject(Project $project): array {
    return $this->entityManager->getRepository(Project::class)->getDaysRemainingProject($project);
  }

  public function getDaysRemaining(User $user): array {
    return $this->entityManager->getRepository(User::class)->getDaysRemaining($user);
  }

  public function getDaysRemainingCar(Car $car): array {
    return $this->entityManager->getRepository(Car::class)->getDaysRemainingCar($car);
  }

  public function getDaysRemainingTask(Task $task): array {
    return $this->entityManager->getRepository(Task::class)->getDaysRemaining($task);
  }
  public function getDaysRemainingChecklist(ManagerChecklist $task): array {
    return $this->entityManager->getRepository(ManagerChecklist::class)->getDaysRemaining($task);
  }

  public function getDaysRemainingPhase(Phase $phase): array {
    return $this->entityManager->getRepository(Phase::class)->getDaysRemainingPhase($phase);
  }

  public function getUcinkovitostProject(Project $project): float {
    return $this->entityManager->getRepository(Phase::class)->calculateProjectEfficiency($project);

  }

  public function getUcinkovitost1(Phase $phase): array {
    return $this->entityManager->getRepository(Task::class)->countTasksPhase($phase);

  }

  public function getUcinkovitost2(Phase $phase): array {
    return $this->entityManager->getRepository(ManagerChecklist::class)->countTasksPhase($phase);
  }


}
