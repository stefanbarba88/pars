<?php

namespace App\Entity;

use App\Classes\Data\PrioritetData;
use App\Classes\Data\TaskStatusData;
use App\Repository\TicketRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Ticket {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;


  #[ORM\ManyToOne(inversedBy: 'tickets')]
  private ?Client $client = null;

  #[ORM\ManyToOne]
  private ?User $contact = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $task = null;

  #[ORM\Column(nullable: true)]
  private ?DateTimeImmutable $deadline;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private ?int $status = TaskStatusData::NIJE_ZAPOCETO;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private ?int $priority = PrioritetData::MEDIUM;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Company $company = null;

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

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
    $this->company = $company;

    return $this;
  }

  public function getClient(): ?Client {
    return $this->client;
  }

  public function setClient(?Client $client): self {
    $this->client = $client;

    return $this;
  }

  public function getContact(): ?User {
    return $this->contact;
  }

  public function setContact(?User $contact): self {
    $this->contact = $contact;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getTask(): ?string {
    return $this->task;
  }

  /**
   * @param string|null $task
   */
  public function setTask(?string $task): void {
    $this->task = $task;
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
   * @return int|null
   */
  public function getPriority(): ?int {
    return $this->priority;
  }

  /**
   * @param int|null $priority
   */
  public function setPriority(?int $priority): void {
    $this->priority = $priority;
  }

}
