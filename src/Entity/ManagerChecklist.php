<?php

namespace App\Entity;

use App\Classes\Data\PrioritetData;
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
  private ?DateTimeImmutable $datumKreiranja = null;

  #[ORM\Column(nullable: true)]
  private ?DateTimeImmutable $deadline;

  #[ORM\Column(nullable: true)]
  private ?int $repeating = 0;

  #[ORM\Column(nullable: true)]
  private ?int $repeatingInterval = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumPonavljanja = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finish = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $finishDesc = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column]
  private ?int $status = TaskStatusData::NIJE_ZAPOCETO;

  #[ORM\OneToMany(mappedBy: 'managerChecklist', targetEntity: Comment::class)]
  private Collection $comment;

  #[ORM\ManyToOne]
  private ?User $user = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private ?int $priority = PrioritetData::MEDIUM;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\ManyToOne(inversedBy: 'managerChecklists')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Project $project = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?Category $category = null;

  #[ORM\OneToMany(mappedBy: 'managerChecklist', targetEntity: Pdf::class, cascade: ["persist", "remove"])]
  private Collection $pdfs;

  public function __construct() {
    $this->pdfs = new ArrayCollection();
    $this->comment = new ArrayCollection();
  }

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
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

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
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

  public function getPriority(): ?int {
    return $this->priority;
  }

  public function setPriority(int $priority): self {
    $this->priority = $priority;

    return $this;
  }

  public function getProject(): ?Project {
    return $this->project;
  }

  public function setProject(?Project $project): self {
    $this->project = $project;

    return $this;
  }

  public function getCategory(): ?Category {
    return $this->category;
  }

  public function setCategory(?Category $category): self {
    $this->category = $category;

    return $this;
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
   * @return int|null
   */
  public function getRepeating(): ?int {
    return $this->repeating;
  }

  /**
   * @param int|null $repeating
   */
  public function setRepeating(?int $repeating): void {
    $this->repeating = $repeating;
  }

  /**
   * @return int|null
   */
  public function getRepeatingInterval(): ?int {
    return $this->repeatingInterval;
  }

  /**
   * @param int|null $repeatingInterval
   */
  public function setRepeatingInterval(?int $repeatingInterval): void {
    $this->repeatingInterval = $repeatingInterval;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumPonavljanja(): ?DateTimeImmutable {
    return $this->datumPonavljanja;
  }

  /**
   * @param DateTimeImmutable|null $datumPonavljanja
   */
  public function setDatumPonavljanja(?DateTimeImmutable $datumPonavljanja): void {
    $this->datumPonavljanja = $datumPonavljanja;
  }

  /**
   * @return Collection<int, Pdf>
   */
  public function getPdfs(): Collection {
    return $this->pdfs;
  }

  public function addPdf(Pdf $pdf): self {
    if (!$this->pdfs->contains($pdf)) {
      $this->pdfs->add($pdf);
      $pdf->setManagerChecklist($this);
    }

    return $this;
  }

  public function removePdf(Pdf $pdf): self {
    if ($this->pdfs->removeElement($pdf)) {
      // set the owning side to null (unless already changed)
      if ($pdf->getManagerChecklist() === $this) {
        $pdf->setManagerChecklist(null);
      }
    }

    return $this;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getDatumKreiranja(): ?DateTimeImmutable {
    return $this->datumKreiranja;
  }

  /**
   * @param DateTimeImmutable|null $datumKreiranja
   */
  public function setDatumKreiranja(?DateTimeImmutable $datumKreiranja): void {
    $this->datumKreiranja = $datumKreiranja;
  }

  /**
   * @return string|null
   */
  public function getFinishDesc(): ?string {
    return $this->finishDesc;
  }

  /**
   * @param string|null $finishDesc
   */
  public function setFinishDesc(?string $finishDesc): void {
    $this->finishDesc = $finishDesc;
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
   * @return Collection<int, Comment>
   */
  public function getComment(): Collection
  {
    return $this->comment;
  }

  public function addComment(Comment $comment): self
  {
    if (!$this->comment->contains($comment)) {
      $this->comment->add($comment);
      $comment->setManagerChecklist($this);
    }

    return $this;
  }

  public function removeComment(Comment $comment): self
  {
    if ($this->comment->removeElement($comment)) {
      // set the owning side to null (unless already changed)
      if ($comment->getManagerChecklist() === $this) {
        $comment->setManagerChecklist(null);
      }
    }

    return $this;
  }
}
