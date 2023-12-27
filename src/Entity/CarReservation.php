<?php

namespace App\Entity;

use App\Repository\CarReservationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'carreservations')]
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
  private ?string $kmStart = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $kmStop = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $descStart = null;
  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $descStop = null;

  #[ORM\Column(nullable: true)]
  private ?int $fuelStart = null;

  #[ORM\Column(nullable: true)]
  private ?int $cleanStart = null;

  #[ORM\Column(nullable: true)]
  private ?int $fuelStop = null;

  #[ORM\Column(nullable: true)]
  private ?int $cleanStop = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finished = null;

  #[ORM\OneToMany(mappedBy: 'carReservation', targetEntity: Image::class, cascade: ["persist", "remove"])]
  private Collection $image;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;
  public function getCompany(): ?Company
  {
    return $this->company;
  }

  public function setCompany(?Company $company): self
  {
    $this->company = $company;

    return $this;
  }
  public function __construct() {
    $this->image = new ArrayCollection();
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

  /**
   * @return string|null
   */
  public function getKmStart(): ?string {
    return $this->kmStart;
  }

  /**
   * @param string|null $kmStart
   */
  public function setKmStart(?string $kmStart): void {
    $this->kmStart = $kmStart;
  }

  /**
   * @return string|null
   */
  public function getKmStop(): ?string {
    return $this->kmStop;
  }

  /**
   * @param string|null $kmStop
   */
  public function setKmStop(?string $kmStop): void {
    $this->kmStop = $kmStop;
  }

  /**
   * @return string|null
   */
  public function getDescStart(): ?string {
    return $this->descStart;
  }

  /**
   * @param string|null $descStart
   */
  public function setDescStart(?string $descStart): void {
    $this->descStart = $descStart;
  }

  /**
   * @return string|null
   */
  public function getDescStop(): ?string {
    return $this->descStop;
  }

  /**
   * @param string|null $descStop
   */
  public function setDescStop(?string $descStop): void {
    $this->descStop = $descStop;
  }

  /**
   * @return int|null
   */
  public function getFuelStart(): ?int {
    return $this->fuelStart;
  }

  /**
   * @param int|null $fuelStart
   */
  public function setFuelStart(?int $fuelStart): void {
    $this->fuelStart = $fuelStart;
  }

  /**
   * @return int|null
   */
  public function getCleanStart(): ?int {
    return $this->cleanStart;
  }

  /**
   * @param int|null $cleanStart
   */
  public function setCleanStart(?int $cleanStart): void {
    $this->cleanStart = $cleanStart;
  }

  /**
   * @return int|null
   */
  public function getFuelStop(): ?int {
    return $this->fuelStop;
  }

  /**
   * @param int|null $fuelStop
   */
  public function setFuelStop(?int $fuelStop): void {
    $this->fuelStop = $fuelStop;
  }

  /**
   * @return int|null
   */
  public function getCleanStop(): ?int {
    return $this->cleanStop;
  }

  /**
   * @param int|null $cleanStop
   */
  public function setCleanStop(?int $cleanStop): void {
    $this->cleanStop = $cleanStop;
  }

  /**
   * @return Collection<int, Image>
   */
  public function getImage(): Collection {
    return $this->image;
  }

  public function addImage(Image $image): self {
    if (!$this->image->contains($image)) {
      $this->image->add($image);
      $image->setCarReservation($this);
    }

    return $this;
  }

  public function removeImage(Image $image): self {
    if ($this->image->removeElement($image)) {
      // set the owning side to null (unless already changed)
      if ($image->getCarReservation() === $this) {
        $image->setCarReservation(null);
      }
    }

    return $this;
  }


}
