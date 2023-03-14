<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'images')]
class Image {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(name: 'original', type: Types::TEXT, nullable: true)]
  private ?string $original = null;

  #[ORM\Column(name: 'thumbnail_100x100', type: Types::TEXT, nullable: true)]
  private ?string $thumbnail100 = null;

  #[ORM\Column(name: 'thumbnail_500x500', type: Types::TEXT, nullable: true)]
  private ?string $thumbnail500 = null;

  #[ORM\ManyToOne]
  private ?User $user = null;

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


  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
    $this->user = $user;

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

  /**
   * @return string|null
   */
  public function getOriginal(): ?string {
    return $this->original;
  }

  /**
   * @param string|null $original
   */
  public function setOriginal(?string $original): void {
    $this->original = $original;
  }

  /**
   * @return string|null
   */
  public function getThumbnail100(): ?string {
    return $this->thumbnail100;
  }

  /**
   * @param string|null $thumbnail100
   */
  public function setThumbnail100(?string $thumbnail100): void {
    $this->thumbnail100 = $thumbnail100;
  }

  /**
   * @return string|null
   */
  public function getThumbnail500(): ?string {
    return $this->thumbnail500;
  }

  /**
   * @param string|null $thumbnail500
   */
  public function setThumbnail500(?string $thumbnail500): void {
    $this->thumbnail500 = $thumbnail500;
  }
}
