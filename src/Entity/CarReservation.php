<?php

namespace App\Entity;

use App\Repository\CarReservationRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'carReservations')]
class CarReservation {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'carReservations')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Car $car = null;

  #[ORM\ManyToOne(inversedBy: 'carReservations')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $driver = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finished = null;

  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
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

  public function getFinished(): ?DateTimeImmutable {
    return $this->finished;
  }

  public function setFinished(?DateTimeImmutable $finished): self {
    $this->finished = $finished;

    return $this;
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getCar(): ?Car {
    return $this->car;
  }

  public function setCar(?Car $car): self {
    $this->car = $car;

    return $this;
  }

  public function getDriver(): ?User {
    return $this->driver;
  }

  public function setDriver(?User $driver): self {
    $this->driver = $driver;

    return $this;
  }


}
