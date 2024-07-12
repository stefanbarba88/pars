<?php

namespace App\Entity;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Repository\AvailabilityRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Availability {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private ?DateTimeImmutable $datum = null;

  #[ORM\Column]
  private ?int $type = AvailabilityData::NEDOSTUPAN;

  #[ORM\Column]
  private ?int $typeDay = TipNeradnihDanaData::RADNI_DAN;

  #[ORM\Column(nullable: true)]
  private ?int $zahtev = null;

  #[ORM\Column(nullable: true)]
  private ?int $calendar = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $User = null;

  #[ORM\Column(nullable: true)]
  private ?int $task = null;

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
    return $this->User;
  }

  public function setUser(?User $User): self {
    $this->User = $User;

    return $this;
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

  /**
   * @return int|null
   */
  public function getType(): ?int {
    return $this->type;
  }

  /**
   * @param int|null $type
   */
  public function setType(?int $type): void {
    $this->type = $type;
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
   * @return int|null
   */
  public function getZahtev(): ?int {
    return $this->zahtev;
  }

  /**
   * @param int|null $zahtev
   */
  public function setZahtev(?int $zahtev): void {
    $this->zahtev = $zahtev;
  }

  /**
   * @return int|null
   */
  public function getCalendar(): ?int {
    return $this->calendar;
  }

  /**
   * @param int|null $calendar
   */
  public function setCalendar(?int $calendar): void {
    $this->calendar = $calendar;
  }

  /**
   * @return int|null
   */
  public function getTypeDay(): ?int {
    return $this->typeDay;
  }

  /**
   * @param int|null $typeDay
   */
  public function setTypeDay(?int $typeDay): void {
    $this->typeDay = $typeDay;
  }




}
