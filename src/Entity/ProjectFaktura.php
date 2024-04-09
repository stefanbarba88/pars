<?php

namespace App\Entity;

use App\Repository\ProjectFakturaRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectFakturaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'project_faktura')]
class ProjectFaktura {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'projectFakturas')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Project $project = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\Column(nullable: true,)]
  private ?int $noTasks = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datum = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;


  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $status = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

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

  public function getProject(): ?Project {
    return $this->project;
  }

  public function setProject(?Project $project): self {
    $this->project = $project;

    return $this;
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
   * @return DateTimeImmutable|null
   */
  public function getDatum(): ?DateTimeImmutable {
    return $this->datum;
  }

  /**
   * @param DateTimeImmutable|null $datum
   */
  public function setDatum(?DateTimeImmutable $datum): void {
    $this->datum = $datum;
  }

  /**
   * @return Company|null
   */
  public function getCompany(): ?Company {
    return $this->company;
  }

  /**
   * @param Company|null $company
   */
  public function setCompany(?Company $company): void {
    $this->company = $company;
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
  public function getNoTasks(): ?int {
    return $this->noTasks;
  }

  /**
   * @param int|null $noTasks
   */
  public function setNoTasks(?int $noTasks): void {
    $this->noTasks = $noTasks;
  }



}
