<?php

namespace App\Entity;

use App\Repository\HolidayRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HolidayRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'holidays')]
class Holiday {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datum = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column]
  private ?int $type = null;


  #[ORM\Column]
  private bool $isSuspended = false;

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

  public function getType(): ?int {
    return $this->type;
  }

  public function setType(int $type): self {
    $this->type = $type;

    return $this;
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

}
