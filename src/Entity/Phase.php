<?php

namespace App\Entity;

use App\Repository\PhaseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhaseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Phase {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $status = 0;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $percent = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $deadline = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne(inversedBy: 'phase')]
  private ?Project $project = null;

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

  public function getTitle(): ?string {
    return $this->title;
  }

  public function setTitle(string $title): self {
    $this->title = $title;

    return $this;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): void {
    $this->description = $description;
  }

  public function getStatus(): ?int {
    return $this->status;
  }

  public function setStatus(?int $status): void {
    $this->status = $status;
  }

  public function getPercent(): ?string {
    return $this->percent;
  }

  public function setPercent(?string $percent): void {
    $this->percent = $percent;
  }

  public function getDeadline(): ?DateTimeImmutable {
    return $this->deadline;
  }

  public function setDeadline(?DateTimeImmutable $deadline): void {
    $this->deadline = $deadline;
  }

  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  public function setCreated(DateTimeImmutable $created): void {
    $this->created = $created;
  }

  public function getUpdated(): DateTimeImmutable {
    return $this->updated;
  }

  public function setUpdated(DateTimeImmutable $updated): void {
    $this->updated = $updated;
  }

  public function getProject(): ?Project
  {
      return $this->project;
  }

  public function setProject(?Project $project): self
  {
      $this->project = $project;

      return $this;
  }


}
