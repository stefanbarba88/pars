<?php

namespace App\Entity;

use App\Classes\Data\FastTaskData;
use App\Repository\PripremaZadatakRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PripremaZadatakRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PripremaZadatak {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'pripremaZadataks')]
  private ?Plan $plan = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description = null;

  #[ORM\Column(nullable: true)]
  private ?int $priorityUserLog = null;

  #[ORM\Column(nullable: true)]
  private ?int $task = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isFree = false;

  #[ORM\Column(nullable: true)]
  private ?int $car = null;

  #[ORM\Column(nullable: true)]
  private ?int $taskType = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver = null;

  #[ORM\ManyToOne(inversedBy: 'plans')]
  private ?Project $project = null;

  #[ORM\ManyToOne(inversedBy: 'plans')]
  private ?Category $category = null;

  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'plans')]
  private Collection $assignedUsers;

  #[ORM\ManyToMany(targetEntity: Activity::class)]
  private Collection $activity;

  #[ORM\ManyToMany(targetEntity: Tool::class, inversedBy: 'plans')]
  private Collection $oprema;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $vreme = null;

  #[ORM\Column]
  private ?int $status = FastTaskData::OPEN;

  public function getId(): ?int {
    return $this->id;
  }

  public function getPlan(): ?Plan {
    return $this->plan;
  }

  public function setPlan(?Plan $plan): self {
    $this->plan = $plan;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return $this->description;
  }

  /**
   * @param string|null $description
   */
  public function setDescription(?string $description): void {
    $this->description = $description;
  }

  /**
   * @return int|null
   */
  public function getPriorityUserLog(): ?int {
    return $this->priorityUserLog;
  }

  /**
   * @param int|null $priorityUserLog
   */
  public function setPriorityUserLog(?int $priorityUserLog): void {
    $this->priorityUserLog = $priorityUserLog;
  }

  /**
   * @return bool|null
   */
  public function getIsFree(): ?bool {
    return $this->isFree;
  }

  /**
   * @param bool|null $isFree
   */
  public function setIsFree(?bool $isFree): void {
    $this->isFree = $isFree;
  }

  /**
   * @return int|null
   */
  public function getCar(): ?int {
    return $this->car;
  }

  /**
   * @param int|null $car
   */
  public function setCar(?int $car): void {
    $this->car = $car;
  }

  /**
   * @return int|null
   */
  public function getDriver(): ?int {
    return $this->driver;
  }

  /**
   * @param int|null $driver
   */
  public function setDriver(?int $driver): void {
    $this->driver = $driver;
  }

  /**
   * @return Project|null
   */
  public function getProject(): ?Project {
    return $this->project;
  }

  /**
   * @param Project|null $project
   */
  public function setProject(?Project $project): void {
    $this->project = $project;
  }

  /**
   * @return Category|null
   */
  public function getCategory(): ?Category {
    return $this->category;
  }

  /**
   * @param Category|null $category
   */
  public function setCategory(?Category $category): void {
    $this->category = $category;
  }


  public function getAssignedUsersIds():array {
    $ids = [];
    foreach ($this->assignedUsers->toArray() as $usr) {
      $ids[] = $usr->getId();
    }
    return $ids;
  }

  public function getActivitiesIds():array {
    $ids = [];
    foreach ($this->activity->toArray() as $act) {
      $ids[] = $act->getId();
    }
    return $ids;
  }

  public function getOpremaIds():array {
    $ids = [];
    foreach ($this->oprema->toArray() as $opr) {
      $ids[] = $opr->getId();
    }
    return $ids;
  }

  /**
   * @return Collection
   */
  public function getAssignedUsers(): Collection {
    return $this->assignedUsers;
  }

  /**
   * @param Collection $assignedUsers
   */
  public function setAssignedUsers(Collection $assignedUsers): void {
    $this->assignedUsers = $assignedUsers;
  }

  /**
   * @return Collection
   */
  public function getActivity(): Collection {
    return $this->activity;
  }

  /**
   * @param Collection $activity
   */
  public function setActivity(Collection $activity): void {
    $this->activity = $activity;
  }

  /**
   * @return Collection
   */
  public function getOprema(): Collection {
    return $this->oprema;
  }

  /**
   * @param Collection $oprema
   */
  public function setOprema(Collection $oprema): void {
    $this->oprema = $oprema;
  }

  /**
   * @return int|null
   */
  public function getStatus(): ?int {
    return $this->status;
  }

  /**
   * @param int|null $status
   */
  public function setStatus(?int $status): void {
    $this->status = $status;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getVreme(): ?DateTimeImmutable {
    return $this->vreme;
  }

  /**
   * @param DateTimeImmutable|null $vreme
   */
  public function setVreme(?DateTimeImmutable $vreme): void {
    $this->vreme = $vreme;
  }

  /**
   * @return int|null
   */
  public function getTaskType(): ?int {
    return $this->taskType;
  }

  /**
   * @param int|null $taskType
   */
  public function setTaskType(?int $taskType): void {
    $this->taskType = $taskType;
  }

  /**
   * @return int|null
   */
  public function getTask(): ?int {
    return $this->task;
  }

  /**
   * @param int|null $task
   */
  public function setTask(?int $task): void {
    $this->task = $task;
  }


}
