<?php

namespace App\Entity;

use App\Classes\Data\TipTroskovaData;
use App\Repository\ExpenseRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'expenses')]
class Expense {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description = null;

  #[ORM\Column(length: 2, nullable: false)]
  private ?int $type = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $price = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $date = null;

  #[ORM\ManyToOne(inversedBy: 'expenses')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Car $car = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $createdBy = null;

  #[ORM\ManyToOne]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

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

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
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

  public function getTypeData(): string {
    return TipTroskovaData::TIP_TROSKOVA[$this->getType()];
  }

  public function getCar(): ?Car {
    return $this->car;
  }

  public function setCar(?Car $car): self {
    $this->car = $car;

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

  public function getCreatedBy(): ?User {
    return $this->createdBy;
  }

  public function setCreatedBy(?User $createdBy): self {
    $this->createdBy = $createdBy;

    return $this;
  }

  public function getEditBy(): ?User {
    return $this->editBy;
  }

  public function setEditBy(?User $editBy): self {
    $this->editBy = $editBy;

    return $this;
  }

  public function getPrice(): ?string {
    return $this->price;
  }

  public function setPrice(?string $price): self {
    $this->price = $price;

    return $this;
  }
  public function getDate(): ?DateTimeImmutable {
    return $this->date;
  }

  public function setDate(?DateTimeImmutable $date): self {
    $this->date = $date;

    return $this;
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

}
