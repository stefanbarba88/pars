<?php

namespace App\Entity;

use App\Repository\CarRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['plate'], message: 'U bazi već postoji vozilo sa ovim registracionim brojem!')]
#[ORM\Table(name: 'cars')]
class Car implements JsonSerializable {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $brand = null;

  #[ORM\Column(length: 255)]
  private ?string $model = null;

  #[ORM\Column(length: 255)]
  private ?string $plate = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $price = null;

  #[ORM\Column(name: 'datum_registracije', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumRegistracije = null;

  #[ORM\Column(name: 'datum_naredne_registracije', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumNaredneRegistracije = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isReserved = false;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $createdBy = null;

  #[ORM\ManyToOne]
  private ?User $editBy = null;

  #[ORM\OneToMany(mappedBy: 'car', targetEntity: CarHistory::class, cascade: ["persist", "remove"])]
  private Collection $carHistories;

  #[ORM\OneToMany(mappedBy: 'car', targetEntity: CarReservation::class)]
  private Collection $carReservations;

  #[ORM\OneToMany(mappedBy: 'car', targetEntity: Expense::class)]
  private Collection $expenses;

  public function __construct() {
    $this->carHistories = new ArrayCollection();
    $this->carReservations = new ArrayCollection();
    $this->expenses = new ArrayCollection();
  }

  public function jsonSerialize(): array {

    return [
      'id' => $this->getId(),
      'brand' => $this->getBrand(),
      'model' => $this->getModel(),
      'plate' => $this->getPlate(),
      'dateReg' => $this->getDatumRegistracije(),
      'dateNextReg' => $this->getDatumNaredneRegistracije(),
      'created' => $this->getCreated(),
      'createdBy' => $this->getCreatedBy()->getFullName(),
      'price' => $this->getPrice(),
      'reservation' => $this->isReserved(),
    ];
  }

  public function getCarName(): ?string {
    return $this->brand . ' - ' . $this->model . ' (' . $this->plate . ')';
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

  public function getId(): ?int {
    return $this->id;
  }

  public function getBrand(): ?string {
    return $this->brand;
  }

  public function setBrand(string $brand): self {
    $this->brand = $brand;

    return $this;
  }

  public function getModel(): ?string {
    return $this->model;
  }

  public function setModel(string $model): self {
    $this->model = $model;

    return $this;
  }

  public function getPlate(): ?string {
    return $this->plate;
  }

  public function setPlate(string $plate): self {
    $this->plate = $plate;

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


  /**
   * @return Collection<int, CarHistory>
   */
  public function getCarHistories(): Collection {
    return $this->carHistories;
  }

  public function addCarHistory(CarHistory $carHistory): self {
    if (!$this->carHistories->contains($carHistory)) {
      $this->carHistories->add($carHistory);
      $carHistory->setCar($this);
    }

    return $this;
  }

  public function removeCarHistory(CarHistory $carHistory): self {
    if ($this->carHistories->removeElement($carHistory)) {
      // set the owning side to null (unless already changed)
      if ($carHistory->getCar() === $this) {
        $carHistory->setCar(null);
      }
    }

    return $this;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumRegistracije(): ?DateTimeImmutable {
    return $this->datumRegistracije;
  }

  /**
   * @param DateTimeImmutable|null $datumRegistracije
   */
  public function setDatumRegistracije(?DateTimeImmutable $datumRegistracije): void {
    $this->datumRegistracije = $datumRegistracije;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumNaredneRegistracije(): ?DateTimeImmutable {
    return $this->datumNaredneRegistracije;
  }

  /**
   * @param DateTimeImmutable|null $datumNaredneRegistracije
   */
  public function setDatumNaredneRegistracije(?DateTimeImmutable $datumNaredneRegistracije): void {
    $this->datumNaredneRegistracije = $datumNaredneRegistracije;
  }

  /**
   * @return Collection<int, CarReservation>
   */
  public function getCarReservations(): Collection
  {
      return $this->carReservations;
  }

  public function addCarReservation(CarReservation $carReservation): self
  {
      if (!$this->carReservations->contains($carReservation)) {
          $this->carReservations->add($carReservation);
          $carReservation->setCar($this);
      }

      return $this;
  }

  public function removeCarReservation(CarReservation $carReservation): self
  {
      if ($this->carReservations->removeElement($carReservation)) {
          // set the owning side to null (unless already changed)
          if ($carReservation->getCar() === $this) {
              $carReservation->setCar(null);
          }
      }

      return $this;
  }

  public function getPrice(): ?string {
    return $this->price;
  }

  public function setPrice(?string $price): self {
    $this->price = $price;

    return $this;
  }

  /**
   * @return bool
   */
  public function isReserved(): bool {
    return $this->isReserved;
  }

  /**
   * @param bool $isReserved
   */
  public function setIsReserved(bool $isReserved): void {
    $this->isReserved = $isReserved;
  }

  /**
   * @return Collection<int, Expense>
   */
  public function getExpenses(): Collection
  {
      return $this->expenses;
  }

  public function addExpense(Expense $expense): self
  {
      if (!$this->expenses->contains($expense)) {
          $this->expenses->add($expense);
          $expense->setCar($this);
      }

      return $this;
  }

  public function removeExpense(Expense $expense): self
  {
      if ($this->expenses->removeElement($expense)) {
          // set the owning side to null (unless already changed)
          if ($expense->getCar() === $this) {
              $expense->setCar(null);
          }
      }

      return $this;
  }



}
