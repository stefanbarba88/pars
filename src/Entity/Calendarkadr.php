<?php

namespace App\Entity;

use App\Repository\CalendarkadrRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalendarkadrRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Calendarkadr {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;


  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $note = null;
  #[ORM\Column(nullable: true)]
  private ?int $days = 0;
  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $aneks = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private ?DateTimeImmutable $start = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finish = null;

  #[ORM\Column]
  private ?int $type = 1;

  #[ORM\Column]
  private ?int $status = 1;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\ManyToOne(inversedBy: 'calendarkadrs')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
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

  public function getStart(): ?DateTimeImmutable {
    return $this->start;
  }

  public function setStart(?DateTimeImmutable $start): self {
    $this->start = $start;

    return $this;
  }

  public function getFinish(): ?DateTimeImmutable {
    return $this->finish;
  }

  public function setFinish(?DateTimeImmutable $finish): self {
    $this->finish = $finish;

    return $this;
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
   * @return string|null
   */
  public function getNote(): ?string {
    return $this->note;
  }

  /**
   * @param string|null $note
   */
  public function setNote(?string $note): void {
    $this->note = $note;
  }

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
    $this->user = $user;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getAneks(): ?string {
    return $this->aneks;
  }

  /**
   * @param string|null $aneks
   */
  public function setAneks(?string $aneks): void {
    $this->aneks = $aneks;
  }

  /**
   * @return int|null
   */
  public function getDays(): ?int {
    return $this->days;
  }

  /**
   * @param int|null $days
   */
  public function setDays(?int $days): void {
    $this->days = $days;
  }


}

