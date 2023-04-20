<?php

namespace App\Entity;

use App\Repository\PdfRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PdfRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Pdf {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $path = null;

  #[ORM\ManyToOne(inversedBy: 'pdfs')]
  private ?Task $task = null;

  #[ORM\ManyToOne(inversedBy: 'pdfs')]
  private ?TaskLog $TaskLog = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

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

  public function getPath(): ?string {
    return $this->path;
  }

  public function setPath(string $path): self {
    $this->path = $path;

    return $this;
  }


  public function getTask(): ?Task {
    return $this->task;
  }

  public function setTask(?Task $task): self {
    $this->task = $task;

    return $this;
  }

  public function getTaskLog(): ?TaskLog {
    return $this->TaskLog;
  }

  public function setTaskLog(?TaskLog $TaskLog): self {
    $this->TaskLog = $TaskLog;

    return $this;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  public function setCreated(DateTimeImmutable $created): self {
    $this->created = $created;

    return $this;
  }

  public function getUpdated(): ?DateTimeImmutable {
    return $this->updated;
  }

  public function setUpdated(DateTimeImmutable $updated): self {
    $this->updated = $updated;

    return $this;
  }
}
