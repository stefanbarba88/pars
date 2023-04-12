<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'tasks')]
class Task implements JsonSerializable {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column(nullable: true)]
  private DateTimeImmutable $deadline;

  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $priority = null;

  #[ORM\Column]
  private ?bool $isEstimate = null;

  #[ORM\Column]
  private ?bool $isClientView = null;

  #[ORM\Column]
  private ?bool $isTimeRoundUp = null;

  #[ORM\Column(nullable: true)]
  private ?int $minEntry = null;

  #[ORM\Column(nullable: true)]
  private ?int $roundingInterval = null;

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
//      'title' => $this->getTitle(),
//      'description' => $this->getDescription(),
//      'isSuspended' => $this->isSuspended(),
//      'isTimeRoundUp' => $this->isTimeRoundUp(),
//      'isEstimate' => $this->isEstimate(),
//      'isClientView' => $this->isClientView(),
//      'category' => $this->getCategoriesJson(),
//      'client' => $this->getClientsJson(),
//      'label' => $this->getLabelJson(),
//      'editBy' => $this->getEditByJson(),
//      'payment' => $this->getPayment(),
//      'price' => $this->getPrice(),
//      'pricePerHour' => $this->getPricePerHour(),
//      'pricePerTask' => $this->getPricePerTask(),
//      'currency' => $this->getCurrencyJson(),
//      'minEntry' => $this->getMinEntry(),
//      'roundingInterval' => $this->getRoundingInterval(),
//      'deadline' => $this->getDeadline()
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

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(string $description): self {
    $this->description = $description;

    return $this;
  }

  public function getDeadline(): ?\DateTimeImmutable {
    return $this->deadline;
  }

  public function setDeadline(?\DateTimeImmutable $deadline): self {
    $this->deadline = $deadline;

    return $this;
  }

  public function getPriority(): ?int {
    return $this->priority;
  }

  public function setPriority(?int $priority): self {
    $this->priority = $priority;

    return $this;
  }

  public function isIsEstimate(): ?bool {
    return $this->isEstimate;
  }

  public function setIsEstimate(bool $isEstimate): self {
    $this->isEstimate = $isEstimate;

    return $this;
  }

  public function isIsClientView(): ?bool {
    return $this->isClientView;
  }

  public function setIsClientView(bool $isClientView): self {
    $this->isClientView = $isClientView;

    return $this;
  }

  public function isIsTimeRoundUp(): ?bool {
    return $this->isTimeRoundUp;
  }

  public function setIsTimeRoundUp(bool $isTimeRoundUp): self {
    $this->isTimeRoundUp = $isTimeRoundUp;

    return $this;
  }

  public function getMinEntry(): ?int {
    return $this->minEntry;
  }

  public function setMinEntry(?int $minEntry): self {
    $this->minEntry = $minEntry;

    return $this;
  }

  public function getRoundingInterval(): ?int {
    return $this->roundingInterval;
  }

  public function setRoundingInterval(?int $roundingInterval): self {
    $this->roundingInterval = $roundingInterval;

    return $this;
  }

  /**
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  /**
   * @param User|null $editBy
   */
  public function setEditBy(?User $editBy): void {
    $this->editBy = $editBy;
  }

  /**
   * @return User|null
   */
  public function getCreatedBy(): ?User {
    return $this->createdBy;
  }

  /**
   * @param User|null $createdBy
   */
  public function setCreatedBy(?User $createdBy): void {
    $this->createdBy = $createdBy;
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
