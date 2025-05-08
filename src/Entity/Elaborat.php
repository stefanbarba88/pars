<?php

namespace App\Entity;

use App\Classes\Data\StatusElaboratData;
use App\Classes\Slugify;
use App\Repository\ElaboratRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ElaboratRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Elaborat {


  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(nullable: true)]
  private ?int $status = StatusElaboratData::KREIRAN;

  #[ORM\Column(nullable: true)]
  private ?int $priority = null;

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


  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'elaborats')]
  private Collection $employee;

  #[ORM\ManyToOne(inversedBy: 'elaborats')]
  private ?Project $project = null;

  #[ORM\OneToMany(mappedBy: 'elaborat', targetEntity: ElaboratInput::class, cascade: ['persist', 'remove'])]
  private Collection $input;


  public function __construct() {
    $this->employee = new ArrayCollection();
    $this->input = new ArrayCollection();
  }

  public function getId(): ?int {
    return $this->id;
  }

  /**
   * @return Collection<int, User>
   */
  public function getEmployee(): Collection {
    return $this->employee;
  }

  public function addEmployee(User $employee): self {
    if (!$this->employee->contains($employee)) {
      $this->employee->add($employee);
    }

    return $this;
  }

  public function removeEmployee(User $employee): self {
    $this->employee->removeElement($employee);

    return $this;
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
  public function getTitle(): ?string {
    return $this->title;
  }

  /**
   * @param string|null $title
   */
  public function setTitle(?string $title): void {
    $this->title = $title;
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

  /**
   * @return Collection<int, ElaboratInput>
   */
  public function getInput(): Collection
  {
      return $this->input;
  }

  public function addInput(ElaboratInput $input): self
  {
      if (!$this->input->contains($input)) {
          $this->input->add($input);
          $input->setElaborat($this);
      }

      return $this;
  }

  public function removeInput(ElaboratInput $input): self
  {
      if ($this->input->removeElement($input)) {
          // set the owning side to null (unless already changed)
          if ($input->getElaborat() === $this) {
              $input->setElaborat(null);
          }
      }

      return $this;
  }



}
