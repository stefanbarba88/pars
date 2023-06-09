<?php

namespace App\Entity;

use App\Repository\CarReservationRepository;
use DateTimeImmutable;
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

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $startKm = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $stopKm = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private DateTimeImmutable $finished;

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

  /**
   * @return DateTimeImmutable
   */
  public function getFinished(): DateTimeImmutable {
    return $this->finished;
  }

  /**
   * @param DateTimeImmutable $finished
   */
  public function setFinished(DateTimeImmutable $finished): void {
    $this->finished = $finished;
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

  /**
   * @return string|null
   */
  public function getStartKm(): ?string {
    return $this->startKm;
  }

  /**
   * @param string|null $startKm
   */
  public function setStartKm(?string $startKm): void {
    $this->startKm = $startKm;
  }

  /**
   * @return string|null
   */
  public function getStopKm(): ?string {
    return $this->stopKm;
  }

  /**
   * @param string|null $stopKm
   */
  public function setStopKm(?string $stopKm): void {
    $this->stopKm = $stopKm;
  }


}
