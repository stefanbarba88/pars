<?php

namespace App\Entity;

use App\Repository\ProjectHistoryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectHistoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProjectHistory {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'projectHistories')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Project $project = null;


  #[ORM\Column]
  private ?DateTimeImmutable $created = null;

  #[ORM\Column]
  private ?DateTimeImmutable $updated = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string  $history = null;

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

  public function getProject(): ?Project {
    return $this->project;
  }

  public function setProject(?Project $project): self {
    $this->project = $project;

    return $this;
  }



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
   * @return string
   */
  public function getHistory(): string {
    return $this->history;
  }

  /**
   * @param string $history
   */
  public function setHistory(string $history): void {
    $this->history = $history;
  }




}