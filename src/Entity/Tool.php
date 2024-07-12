<?php

namespace App\Entity;

use App\Repository\ToolRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ToolRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'tools')]
class Tool implements JsonSerializable {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $serial = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $model = null;

  #[ORM\Column]
  private bool $sertifikat = false;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isReserved = false;


  #[ORM\Column(nullable: true)]
  private ?int $ocena = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumSertifikata = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $createdBy = null;

  #[ORM\ManyToOne]
  private ?User $editBy = null;

  #[ORM\OneToMany(mappedBy: 'tool', targetEntity: ToolHistory::class, cascade: ["persist", "remove"])]
  private Collection $toolHistories;

  #[ORM\OneToMany(mappedBy: 'tool', targetEntity: ToolReservation::class)]
  private Collection $toolReservations;

  #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'oprema')]
  private Collection $tasks;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?ToolType $type = null;

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
    $this->toolHistories = new ArrayCollection();
    $this->toolReservations = new ArrayCollection();
    $this->tasks = new ArrayCollection();
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

  public function jsonSerialize(): array {

    return [
      'id' => $this->getId(),
      'title' => $this->getTitle(),
      'serial' => $this->getSerial(),
      'model' => $this->getModel(),
      'sertifikat' => $this->isSertifikat(),
      'type' => $this->getType(),
      'ocena' => $this->getOcena(),
      'dateSertifikat' => $this->getDatumSertifikata(),
      'created' => $this->getCreated(),
    ];
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

  public function getSerial(): ?string {
    return $this->serial;
  }

  public function setSerial(?string $serial): self {
    $this->serial = $serial;

    return $this;
  }

  /**
   * @return bool
   */
  public function isSertifikat(): bool {
    return $this->sertifikat;
  }

  /**
   * @param bool $sertifikat
   */
  public function setSertifikat(bool $sertifikat): void {
    $this->sertifikat = $sertifikat;
  }

  /**
   * @return string|null
   */
  public function getDescription(): ?string {
    return $this->description;
  }

  /**
   * @param string|null $description
   */
  public function setDescription(?string $description): void {
    $this->description = $description;
  }

  /**
   * @return ToolType|null
   */
  public function getType(): ?ToolType {
    return $this->type;
  }

  /**
   * @param ToolType|null $type
   */
  public function setType(?ToolType $type): void {
    $this->type = $type;
  }


  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumSertifikata(): ?DateTimeImmutable {
    return $this->datumSertifikata;
  }

  /**
   * @param DateTimeImmutable|null $datumSertifikata
   */
  public function setDatumSertifikata(?DateTimeImmutable $datumSertifikata): void {
    $this->datumSertifikata = $datumSertifikata;
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

  /**
   * @return string|null
   */
  public function getModel(): ?string {
    return $this->model;
  }

  /**
   * @param string|null $model
   */
  public function setModel(?string $model): void {
    $this->model = $model;
  }

  /**
   * @return int|null
   */
  public function getOcena(): ?int {
    return $this->ocena;
  }

  /**
   * @param int|null $ocena
   */
  public function setOcena(?int $ocena): void {
    $this->ocena = $ocena;
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
   * @return Collection<int, ToolHistory>
   */
  public function getToolHistories(): Collection {
    return $this->toolHistories;
  }

  public function addToolHistory(ToolHistory $toolHistory): self {
    if (!$this->toolHistories->contains($toolHistory)) {
      $this->toolHistories->add($toolHistory);
      $toolHistory->setTool($this);
    }

    return $this;
  }

  public function removeToolHistory(ToolHistory $toolHistory): self {
    if ($this->toolHistories->removeElement($toolHistory)) {
      // set the owning side to null (unless already changed)
      if ($toolHistory->getTool() === $this) {
        $toolHistory->setTool(null);
      }
    }

    return $this;
  }

  /**
   * @return bool|null
   */
  public function getIsReserved(): ?bool {
    return $this->isReserved;
  }

  /**
   * @param bool|null $isReserved
   */
  public function setIsReserved(?bool $isReserved): void {
    $this->isReserved = $isReserved;
  }

  /**
   * @return Collection<int, ToolReservation>
   */
  public function getToolReservations(): Collection
  {
      return $this->toolReservations;
  }

  public function addToolReservation(ToolReservation $toolReservation): self
  {
      if (!$this->toolReservations->contains($toolReservation)) {
          $this->toolReservations->add($toolReservation);
          $toolReservation->setTool($this);
      }

      return $this;
  }

  public function removeToolReservation(ToolReservation $toolReservation): self
  {
      if ($this->toolReservations->removeElement($toolReservation)) {
          // set the owning side to null (unless already changed)
          if ($toolReservation->getTool() === $this) {
              $toolReservation->setTool(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, Task>
   */
  public function getTasks(): Collection
  {
      return $this->tasks;
  }

  public function addTask(Task $task): self
  {
      if (!$this->tasks->contains($task)) {
          $this->tasks->add($task);
          $task->addOprema($this);
      }

      return $this;
  }

  public function removeTask(Task $task): self
  {
      if ($this->tasks->removeElement($task)) {
          $task->removeOprema($this);
      }

      return $this;
  }



}
