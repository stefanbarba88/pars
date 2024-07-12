<?php

namespace App\Entity;

use App\Classes\Data\FastTaskData;
use App\Repository\PlanRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Plan {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  public function getId(): ?int {
    return $this->id;
  }

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumKreiranja = null;

//  #[ORM\Column(type: Types::TEXT)]
//  private ?string $plan = null;
//
//  #[ORM\Column(type: Types::TEXT)]
//  private ?string $planInterni = null;

//  #[ORM\Column(type: Types::TEXT, nullable: true)]
//  private ?string $description = null;
//
//  #[ORM\Column(nullable: true)]
//  private ?int $priorityUserLog = null;
//
//  #[ORM\Column(nullable: true)]
//  private ?bool $isFree = false;
//
//  #[ORM\Column(nullable: true)]
//  private ?int $car = null;
//
//  #[ORM\Column(nullable: true)]
//  private ?int $driver = null;

  #[ORM\Column]
  private ?int $status = FastTaskData::OPEN;

//  #[ORM\ManyToOne(inversedBy: 'plans')]
//  private ?Project $project = null;
//
//  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'plans')]
//  private Collection $assignedUsers;
//
//  #[ORM\ManyToMany(targetEntity: Activity::class)]
//  private Collection $activity;
//
//  #[ORM\ManyToMany(targetEntity: Tool::class, inversedBy: 'plans')]
//  private Collection $oprema;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\OneToMany(mappedBy: 'plan', targetEntity: PripremaZadatak::class, cascade: ['persist', 'remove'])]
  private Collection $pripremaZadataks;

  public function __construct()
  {
      $this->pripremaZadataks = new ArrayCollection();
  }

//  public function __construct() {
//    $this->assignedUsers = new ArrayCollection();
//    $this->activity = new ArrayCollection();
//    $this->oprema = new ArrayCollection();
//  }

  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
  }

  /**
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  /**
   * @param User|null $editBy
   */
  public function setEditBy(?User $editBy): void {
    $this->editBy = $editBy;
  }

  /**
   * @return User|null
   */
  public function getCreatedBy(): ?User {
    return $this->createdBy;
  }

  /**
   * @param User|null $createdBy
   */
  public function setCreatedBy(?User $createdBy): void {
    $this->createdBy = $createdBy;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  /**
   * @param DateTimeImmutable $created
   */
  public function setCreated(DateTimeImmutable $created): void {
    $this->created = $created;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getUpdated(): DateTimeImmutable {
    return $this->updated;
  }

  /**
   * @param DateTimeImmutable $updated
   */
  public function setUpdated(DateTimeImmutable $updated): void {
    $this->updated = $updated;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumKreiranja(): ?DateTimeImmutable {
    return $this->datumKreiranja;
  }

  /**
   * @param DateTimeImmutable|null $datumKreiranja
   */
  public function setDatumKreiranja(?DateTimeImmutable $datumKreiranja): void {
    $this->datumKreiranja = $datumKreiranja;
  }

//  /**
//   * @return string|null
//   */
//  public function getDescription(): ?string {
//    return $this->description;
//  }
//
//  /**
//   * @param string|null $description
//   */
//  public function setDescription(?string $description): void {
//    $this->description = $description;
//  }
//
//  /**
//   * @return int|null
//   */
//  public function getPriorityUserLog(): ?int {
//    return $this->priorityUserLog;
//  }
//
//  /**
//   * @param int|null $priorityUserLog
//   */
//  public function setPriorityUserLog(?int $priorityUserLog): void {
//    $this->priorityUserLog = $priorityUserLog;
//  }
//
//  /**
//   * @return bool|null
//   */
//  public function getIsFree(): ?bool {
//    return $this->isFree;
//  }
//
//  /**
//   * @param bool|null $isFree
//   */
//  public function setIsFree(?bool $isFree): void {
//    $this->isFree = $isFree;
//  }
//
//  /**
//   * @return int|null
//   */
//  public function getCar(): ?int {
//    return $this->car;
//  }
//
//  /**
//   * @param int|null $car
//   */
//  public function setCar(?int $car): void {
//    $this->car = $car;
//  }
//
//  /**
//   * @return int|null
//   */
//  public function getDriver(): ?int {
//    return $this->driver;
//  }
//
//  /**
//   * @param int|null $driver
//   */
//  public function setDriver(?int $driver): void {
//    $this->driver = $driver;
//  }

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

//  /**
//   * @return Project|null
//   */
//  public function getProject(): ?Project {
//    return $this->project;
//  }
//
//  /**
//   * @param Project|null $project
//   */
//  public function setProject(?Project $project): void {
//    $this->project = $project;
//  }
//
//  /**
//   * @return Collection
//   */
//  public function getAssignedUsers(): Collection {
//    return $this->assignedUsers;
//  }
//
//  /**
//   * @param Collection $assignedUsers
//   */
//  public function setAssignedUsers(Collection $assignedUsers): void {
//    $this->assignedUsers = $assignedUsers;
//  }
//
//  /**
//   * @return Collection
//   */
//  public function getActivity(): Collection {
//    return $this->activity;
//  }
//
//  /**
//   * @param Collection $activity
//   */
//  public function setActivity(Collection $activity): void {
//    $this->activity = $activity;
//  }
//
//  /**
//   * @return Collection
//   */
//  public function getOprema(): Collection {
//    return $this->oprema;
//  }
//
//  /**
//   * @param Collection $oprema
//   */
//  public function setOprema(Collection $oprema): void {
//    $this->oprema = $oprema;
//  }

  /**
   * @return Company|null
   */
  public function getCompany(): ?Company {
    return $this->company;
  }

  /**
   * @param Company|null $company
   */
  public function setCompany(?Company $company): void {
    $this->company = $company;
  }
//
//  public function getPlan(): array {
//    return json_decode($this->plan, true);
//  }
//
//  public function setPlan(?array $plan): self {
//    $this->plan = json_encode($plan);
//
//    return $this;
//  }
//
//  public function getPlanInterni(): array {
//    return json_decode($this->planInterni, true);
//  }
//
//  public function setPlanInterni(?array $planInterni): self {
//    $this->planInterni = json_encode($planInterni);
//
//    return $this;
//  }

  /**
   * @return Collection<int, PripremaZadatak>
   */
  public function getPripremaZadataks(): Collection
  {
      return $this->pripremaZadataks;
  }

  public function addPripremaZadatak(PripremaZadatak $pripremaZadatak): self
  {
      if (!$this->pripremaZadataks->contains($pripremaZadatak)) {
          $this->pripremaZadataks->add($pripremaZadatak);
          $pripremaZadatak->setPlan($this);
      }

      return $this;
  }

  public function removePripremaZadatak(PripremaZadatak $pripremaZadatak): self
  {
      if ($this->pripremaZadataks->removeElement($pripremaZadatak)) {
          // set the owning side to null (unless already changed)
          if ($pripremaZadatak->getPlan() === $this) {
              $pripremaZadatak->setPlan(null);
          }
      }

      return $this;
  }

}
