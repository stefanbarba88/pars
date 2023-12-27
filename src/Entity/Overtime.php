<?php

namespace App\Entity;

use App\Repository\OvertimeRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OvertimeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'overtimes')]
class Overtime {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'overtimes')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\ManyToOne]
  private ?Task $task = null;

  #[ORM\Column(nullable: true)]
  private ?int $hours = null;

  #[ORM\Column(nullable: true)]
  private ?int $minutes = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datum = null;

  #[ORM\Column]
  private int $status = 0;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;
  public function getCompany(): ?Company
  {
    return $this->company;
  }

  public function setCompany(?Company $company): self
  {
    $this->company = $company;

    return $this;
  }
  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
    $this->user = $user;

    return $this;
  }

  public function getTask(): ?Task {
    return $this->task;
  }

  public function setTask(?Task $task): self {
    $this->task = $task;

    return $this;
  }

  public function getHours(): ?int {
    return $this->hours;
  }

  public function setHours(?int $hours): self {
    $this->hours = $hours;

    return $this;
  }

  public function getMinutes(): ?int {
    return $this->minutes;
  }

  public function setMinutes(int $minutes): self {
    $this->minutes = $minutes;

    return $this;
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
   * @return int
   */
  public function getStatus(): int {
    return $this->status;
  }

  /**
   * @param int $status
   */
  public function setStatus(int $status): void {
    $this->status = $status;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatum(): ?DateTimeImmutable {
    return $this->datum;
  }

  /**
   * @param DateTimeImmutable|null $datum
   */
  public function setDatum(?DateTimeImmutable $datum): void {
    $this->datum = $datum;
  }


}
