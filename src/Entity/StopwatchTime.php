<?php

namespace App\Entity;

use App\Repository\StopwatchTimeRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StopwatchTimeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'stopwatchTimes')]
class StopwatchTime {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private ?\DateTimeImmutable $start = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private ?\DateTimeImmutable $stop = null;

  #[ORM\Column]
  private ?int $diff = null;

  #[ORM\Column]
  private ?int $diffRounded = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
  private ?string $lon = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8)]
  private ?string $lat = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne(inversedBy: 'stopwatchTimes')]
  #[ORM\JoinColumn(nullable: false)]
  private ?TaskLog $taskLog = null;

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

  public function getStart(): ?\DateTimeImmutable {
    return $this->start;
  }

  public function setStart(\DateTimeImmutable $start): self {
    $this->start = $start;

    return $this;
  }

  public function getStop(): ?\DateTimeImmutable {
    return $this->stop;
  }

  public function setStop(\DateTimeImmutable $stop): self {
    $this->stop = $stop;

    return $this;
  }

  public function getDiff(): ?int {
    return $this->diff;
  }

  public function setDiff(int $diff): self {
    $this->diff = $diff;

    return $this;
  }

  public function getDiffRounded(): ?int {
    return $this->diffRounded;
  }

  public function setDiffRounded(int $diffRounded): self {
    $this->diffRounded = $diffRounded;

    return $this;
  }

  public function getLon(): ?string {
    return $this->lon;
  }

  public function setLon(string $lon): self {
    $this->lon = $lon;

    return $this;
  }

  public function getLat(): ?string {
    return $this->lat;
  }

  public function setLat(string $lat): self {
    $this->lat = $lat;

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

  public function getTaskLog(): ?TaskLog {
    return $this->taskLog;
  }

  public function setTaskLog(?TaskLog $taskLog): self {
    $this->taskLog = $taskLog;

    return $this;
  }
}
