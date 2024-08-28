<?php

namespace App\Entity;

use App\Classes\Data\ExportData;
use App\Repository\SettingsRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'settings')]
class Settings {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isTimeRoundUp = true;

  #[ORM\Column(nullable: true)]
  private ?int $minEntry = 30;

  #[ORM\Column(nullable: true)]
  private ?int $roundingInterval = 15;

  #[ORM\Column(nullable: true)]
  private ?int $minKm = 500;

  #[ORM\Column(nullable: true)]
  private ?int $workWeek = 6;



  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isClientView = false;

  #[ORM\Column]
  private bool $isBasic = true;

  #[ORM\Column]
  private bool $isGeolocation = false;

  #[ORM\Column]
  private bool $isCar = false;

  #[ORM\Column]
  private bool $isPlan = false;

  #[ORM\Column]
  private bool $isWidgete = true;

  #[ORM\Column]
  private bool $isPlanToday = true;

  #[ORM\Column]
  private bool $isPlanTomorrow = false;

  #[ORM\Column]
  private bool $isPlanEmployee = true;

  #[ORM\Column]
  private bool $isTool = false;

  #[ORM\Column]
  private bool $isCalendar = false;

  #[ORM\Column(nullable: true)]
  private ?int $export = ExportData::BASIC;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $radnoVreme = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToOne(inversedBy: 'settings', cascade: ['persist', 'remove'])]
  #[ORM\JoinColumn(nullable: false)]
  private ?Company $company = null;

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


  /**
   * @return string|null
   */
  public function getTitle(): ?string {
    return $this->title;
  }

  /**
   * @param string|null $title
   */
  public function setTitle(?string $title): void {
    $this->title = $title;
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
   * @return bool
   */
  public function isSuspended(): bool {
    return $this->isSuspended;
  }

  /**
   * @param bool $isSuspended
   */
  public function setIsSuspended(bool $isSuspended): void {
    $this->isSuspended = $isSuspended;
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
   * @return bool|null
   */
  public function getIsTimeRoundUp(): ?bool {
    return $this->isTimeRoundUp;
  }

  /**
   * @param bool|null $isTimeRoundUp
   */
  public function setIsTimeRoundUp(?bool $isTimeRoundUp): void {
    $this->isTimeRoundUp = $isTimeRoundUp;
  }

  /**
   * @return int|null
   */
  public function getMinEntry(): ?int {
    return $this->minEntry;
  }

  /**
   * @param int|null $minEntry
   */
  public function setMinEntry(?int $minEntry): void {
    $this->minEntry = $minEntry;
  }

  /**
   * @return int|null
   */
  public function getRoundingInterval(): ?int {
    return $this->roundingInterval;
  }

  /**
   * @param int|null $roundingInterval
   */
  public function setRoundingInterval(?int $roundingInterval): void {
    $this->roundingInterval = $roundingInterval;
  }

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(Company $company): self {
    $this->company = $company;

    return $this;
  }

  /**
   * @return int|null
   */
  public function getWorkWeek(): ?int {
    return $this->workWeek;
  }

  /**
   * @param int|null $workWeek
   */
  public function setWorkWeek(?int $workWeek): void {
    $this->workWeek = $workWeek;
  }

  /**
   * @return bool
   */
  public function isBasic(): bool {
    return $this->isBasic;
  }

  /**
   * @param bool $isBasic
   */
  public function setIsBasic(bool $isBasic): void {
    $this->isBasic = $isBasic;
  }

  /**
   * @return bool
   */
  public function isCar(): bool {
    return $this->isCar;
  }

  /**
   * @param bool $isCar
   */
  public function setIsCar(bool $isCar): void {
    $this->isCar = $isCar;
  }

  /**
   * @return bool
   */
  public function isTool(): bool {
    return $this->isTool;
  }

  /**
   * @param bool $isTool
   */
  public function setIsTool(bool $isTool): void {
    $this->isTool = $isTool;
  }

  /**
   * @return bool
   */
  public function isCalendar(): bool {
    return $this->isCalendar;
  }

  /**
   * @param bool $isCalendar
   */
  public function setIsCalendar(bool $isCalendar): void {
    $this->isCalendar = $isCalendar;
  }

  /**
   * @return int|null
   */
  public function getExport(): ?int {
    return $this->export;
  }

  /**
   * @param int|null $export
   */
  public function setExport(?int $export): void {
    $this->export = $export;
  }

  /**
   * @return int|null
   */
  public function getMinKm(): ?int {
    return $this->minKm;
  }

  /**
   * @param int|null $minKm
   */
  public function setMinKm(?int $minKm): void {
    $this->minKm = $minKm;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getRadnoVreme(): ?DateTimeImmutable {
    return $this->radnoVreme;
  }

  /**
   * @param DateTimeImmutable|null $radnoVreme
   */
  public function setRadnoVreme(?DateTimeImmutable $radnoVreme): void {
    $this->radnoVreme = $radnoVreme;
  }

  /**
   * @return bool
   */
  public function getIsClientView(): bool {
    return $this->isClientView;
  }

  /**
   * @param bool $isClientView
   */
  public function setIsClientView(bool $isClientView): void {
    $this->isClientView = $isClientView;
  }

  /**
   * @return bool
   */
  public function isWidgete(): bool {
    return $this->isWidgete;
  }

  /**
   * @param bool $isWidgete
   */
  public function setIsWidgete(bool $isWidgete): void {
    $this->isWidgete = $isWidgete;
  }

  /**
   * @return bool
   */
  public function isPlanToday(): bool {
    return $this->isPlanToday;
  }

  /**
   * @param bool $isPlanToday
   */
  public function setIsPlanToday(bool $isPlanToday): void {
    $this->isPlanToday = $isPlanToday;
  }

  /**
   * @return bool
   */
  public function isPlanTomorrow(): bool {
    return $this->isPlanTomorrow;
  }

  /**
   * @param bool $isPlanTomorrow
   */
  public function setIsPlanTomorrow(bool $isPlanTomorrow): void {
    $this->isPlanTomorrow = $isPlanTomorrow;
  }

  /**
   * @return bool
   */
  public function isPlanEmployee(): bool {
    return $this->isPlanEmployee;
  }

  /**
   * @param bool $isPlanEmployee
   */
  public function setIsPlanEmployee(bool $isPlanEmployee): void {
    $this->isPlanEmployee = $isPlanEmployee;
  }

  /**
   * @return bool
   */
  public function isPlan(): bool {
    return $this->isPlan;
  }

  /**
   * @param bool $isPlan
   */
  public function setIsPlan(bool $isPlan): void {
    $this->isPlan = $isPlan;
  }

  /**
   * @return bool
   */
  public function isGeolocation(): bool {
    return $this->isGeolocation;
  }

  /**
   * @param bool $isGeolocation
   */
  public function setIsGeolocation(bool $isGeolocation): void {
    $this->isGeolocation = $isGeolocation;
  }




}
