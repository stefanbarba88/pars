<?php

namespace App\Entity;

use App\Classes\Data\TaskStatusData;
use App\Repository\ManagerChecklistRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagerChecklistRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'checklists')]
class ManagerChecklist {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string $task = null;


  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finish = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private ?int $status = 0;

  #[ORM\ManyToOne]
  private ?User $user = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\Column]
  private ?int $priority = null;

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

  public function getTask(): ?string {
    return $this->task;
  }

  public function setTask(string $task): self {
    $this->task = $task;

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

  public function getFinish(): ?DateTimeImmutable {
    return $this->finish;
  }

  public function setFinish(?DateTimeImmutable $finish): self {
    $this->finish = $finish;

    return $this;
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

  public function getUser(): ?User
  {
      return $this->user;
  }

  public function setUser(?User $user): self
  {
      $this->user = $user;

      return $this;
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

  public function getPriority(): ?int
  {
      return $this->priority;
  }

  public function setPriority(int $priority): self
  {
      $this->priority = $priority;

      return $this;
  }

}
