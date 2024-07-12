<?php

namespace App\Entity;

use App\Repository\VacationRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VacationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vacation {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(nullable: true)]
  private ?int $old = 0;

  #[ORM\Column(nullable: true)]
  private ?int $oldUsed = 0;

  #[ORM\Column(nullable: true)]
  private ?int $new = 0;

  #[ORM\Column(nullable: true)]
  private ?int $used1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacation1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacationk1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacationd1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $other1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $stopwatch1 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $used2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacation2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacationk2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $vacationd2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $stopwatch2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $other2 = 0;

  #[ORM\Column(nullable: true)]
  private ?int $slava = 0;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $history = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Company $company = null;

  #[ORM\OneToOne(inversedBy: 'vacation', cascade: ['persist', 'remove'])]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;


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

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
    $this->company = $company;

    return $this;
  }

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(User $user): self {
    $this->user = $user;

    return $this;
  }

  /**
   * @return int|null
   */
  public function getOld(): ?int {
    return $this->old;
  }

  /**
   * @param int|null $old
   */
  public function setOld(?int $old): void {
    $this->old = $old;
  }

  /**
   * @return int|null
   */
  public function getUsed1(): ?int {
    return $this->used1;
  }

  /**
   * @param int|null $used1
   */
  public function setUsed1(?int $used1): void {
    $this->used1 = $used1;
  }

  /**
   * @return int|null
   */
  public function getVacation1(): ?int {
    return $this->vacation1;
  }

  /**
   * @param int|null $vacation1
   */
  public function setVacation1(?int $vacation1): void {
    $this->vacation1 = $vacation1;
  }

  /**
   * @return int|null
   */
  public function getVacationk1(): ?int {
    return $this->vacationk1;
  }

  /**
   * @param int|null $vacationk1
   */
  public function setVacationk1(?int $vacationk1): void {
    $this->vacationk1 = $vacationk1;
  }

  /**
   * @return int|null
   */
  public function getVacationd1(): ?int {
    return $this->vacationd1;
  }

  /**
   * @param int|null $vacationd1
   */
  public function setVacationd1(?int $vacationd1): void {
    $this->vacationd1 = $vacationd1;
  }

  /**
   * @return int|null
   */
  public function getOther1(): ?int {
    return $this->other1;
  }

  /**
   * @param int|null $other1
   */
  public function setOther1(?int $other1): void {
    $this->other1 = $other1;
  }

  /**
   * @return int|null
   */
  public function getStopwatch1(): ?int {
    return $this->stopwatch1;
  }

  /**
   * @param int|null $stopwatch1
   */
  public function setStopwatch1(?int $stopwatch1): void {
    $this->stopwatch1 = $stopwatch1;
  }

  /**
   * @return int|null
   */
  public function getUsed2(): ?int {
    return $this->used2;
  }

  /**
   * @param int|null $used2
   */
  public function setUsed2(?int $used2): void {
    $this->used2 = $used2;
  }

  /**
   * @return int|null
   */
  public function getVacation2(): ?int {
    return $this->vacation2;
  }

  /**
   * @param int|null $vacation2
   */
  public function setVacation2(?int $vacation2): void {
    $this->vacation2 = $vacation2;
  }

  /**
   * @return int|null
   */
  public function getVacationk2(): ?int {
    return $this->vacationk2;
  }

  /**
   * @param int|null $vacationk2
   */
  public function setVacationk2(?int $vacationk2): void {
    $this->vacationk2 = $vacationk2;
  }

  /**
   * @return int|null
   */
  public function getVacationd2(): ?int {
    return $this->vacationd2;
  }

  /**
   * @param int|null $vacationd2
   */
  public function setVacationd2(?int $vacationd2): void {
    $this->vacationd2 = $vacationd2;
  }

  /**
   * @return int|null
   */
  public function getStopwatch2(): ?int {
    return $this->stopwatch2;
  }

  /**
   * @param int|null $stopwatch2
   */
  public function setStopwatch2(?int $stopwatch2): void {
    $this->stopwatch2 = $stopwatch2;
  }

  /**
   * @return int|null
   */
  public function getOther2(): ?int {
    return $this->other2;
  }

  /**
   * @param int|null $other2
   */
  public function setOther2(?int $other2): void {
    $this->other2 = $other2;
  }

  /**
   * @return int|null
   */
  public function getSlava(): ?int {
    return $this->slava;
  }

  /**
   * @param int|null $slava
   */
  public function setSlava(?int $slava): void {
    $this->slava = $slava;
  }

  /**
   * @return string|null
   */
  public function getHistory(): ?string {
    return $this->history;
  }

  /**
   * @param string|null $history
   */
  public function setHistory(?string $history): void {
    $this->history = $history;
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
  public function getNew(): ?int {
    return $this->new;
  }

  /**
   * @param int|null $new
   */
  public function setNew(?int $new): void {
    $this->new = $new;
  }

  /**
   * @return int|null
   */
  public function getOldUsed(): ?int {
    return $this->oldUsed;
  }

  /**
   * @param int|null $oldUsed
   */
  public function setOldUsed(?int $oldUsed): void {
    $this->oldUsed = $oldUsed;
  }

  public function getNewUkupno(): ?int {
    return $this->vacation1 + $this->vacation2 + $this->vacationk1 + $this->vacationk2 + $this->vacationd1 + $this->vacationd2;
  }
}
