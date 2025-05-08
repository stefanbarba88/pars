<?php

namespace App\Entity;

use App\Repository\ElaboratInputRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElaboratInputRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ElaboratInput {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\Column(nullable: true)]
  private ?int $status = null;

  #[ORM\Column(nullable: true)]
  private ?float $percent = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $deadline = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $estimate = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $send = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne]
  private ?User $createdBy = null;

  #[ORM\ManyToOne(inversedBy: 'input')]
  private ?Elaborat $elaborat = null;

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

  public function __clone() {
    if ($this->id) {
      $this->id = null;
    }
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
   * @return int|null
   */
  public function getStatus(): ?int {
    return $this->status;
  }

  /**
   * @param int|null $status
   */
  public function setStatus(?int $status): void {
    $this->status = $status;
  }

  /**
   * @return float|null
   */
  public function getPercent(): ?float {
    return $this->percent;
  }

  /**
   * @param float|null $percent
   */
  public function setPercent(?float $percent): void {
    $this->percent = $percent;
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
   * @return DateTimeImmutable|null
   */
  public function getDeadline(): ?DateTimeImmutable {
    return $this->deadline;
  }

  /**
   * @param DateTimeImmutable|null $deadline
   */
  public function setDeadline(?DateTimeImmutable $deadline): void {
    $this->deadline = $deadline;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getEstimate(): ?DateTimeImmutable {
    return $this->estimate;
  }

  /**
   * @param DateTimeImmutable|null $estimate
   */
  public function setEstimate(?DateTimeImmutable $estimate): void {
    $this->estimate = $estimate;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getSend(): ?DateTimeImmutable {
    return $this->send;
  }

  /**
   * @param DateTimeImmutable|null $send
   */
  public function setSend(?DateTimeImmutable $send): void {
    $this->send = $send;
  }

  public function getElaborat(): ?Elaborat
  {
      return $this->elaborat;
  }

  public function setElaborat(?Elaborat $elaborat): self
  {
      $this->elaborat = $elaborat;

      return $this;
  }

}
