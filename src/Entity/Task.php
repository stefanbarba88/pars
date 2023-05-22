<?php

namespace App\Entity;

use App\Classes\Slugify;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'tasks')]
class Task implements JsonSerializable {

  public function getUploadPath(): ?string {
    return $this->getProject()->getUploadPath() . Slugify::slugify($this->title) . '/';
  }

  public function getThumbUploadPath(): ?string {
    return $this->getProject()->getThumbUploadPath() . Slugify::slugify($this->title) . '/';
  }

  public function getNoProjectUploadPath(): ?string {
    return $this->getNoProjectUploadPath() . Slugify::slugify($this->title) . '/';
  }

  public function getNoProjectThumbUploadPath(): ?string {
    return $this->getNoProjectThumbUploadPath() . Slugify::slugify($this->title) . '/';
  }

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
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
  private ?DateTimeImmutable $deadline;

  #[ORM\Column]
  private ?bool $isPriority = false;

  #[ORM\Column(nullable: true)]
  private ?bool $isEstimate = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isClientView = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isExpenses = null;

  #[ORM\Column]
  private ?bool $isDeleted = false;

  #[ORM\Column]
  private ?bool $isClosed = false;

  #[ORM\Column(nullable: true)]
  private ?bool $isTimeRoundUp = null;

  #[ORM\Column(nullable: true)]
  private ?int $minEntry = null;

  #[ORM\Column(nullable: true)]
  private ?int $roundingInterval = null;

  #[ORM\ManyToOne(inversedBy: 'tasks')]
  private ?Project $project = null;

  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'tasks')]
  private Collection $assignedUsers;

  #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskHistory::class, cascade: ["persist", "remove"])]
  private Collection $taskHistories;

  #[ORM\ManyToOne(targetEntity: self::class)]
  private ?self $parentTask = null;

  #[ORM\OneToMany(mappedBy: 'task', targetEntity: Pdf::class, cascade: ["persist", "remove"])]
  private Collection $pdfs;

  #[ORM\ManyToOne(inversedBy: 'tasks')]
  private ?Category $category = null;

  #[ORM\ManyToMany(targetEntity: Label::class, inversedBy: 'tasks')]
  private Collection $label;

  #[ORM\OneToMany(mappedBy: 'task', targetEntity: TaskLog::class, cascade: ["persist", "remove"], orphanRemoval: true)]
  private Collection $taskLogs;

  #[ORM\OneToMany(mappedBy: 'Task', targetEntity: Comment::class)]
  private Collection $comments;


  public function __construct() {
    $this->assignedUsers = new ArrayCollection();
    $this->taskHistories = new ArrayCollection();
    $this->pdfs = new ArrayCollection();
    $this->label = new ArrayCollection();
    $this->taskLogs = new ArrayCollection();
    $this->comments = new ArrayCollection();
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
      'project' => $this->getProjectJson(),
      'description' => $this->getDescription(),
      'isTimeRoundUp' => $this->isIsTimeRoundUp(),
      'isEstimate' => $this->isIsEstimate(),
      'isClientView' => $this->isIsClientView(),
      'labels' => $this->getLabelJson(),
      'category' => $this->getCategoryJson(),
      'isExpenses' => $this->isIsExpenses(),
      'editBy' => $this->getEditByJson(),
      'parentTask' => $this->getParentTaskJson(),
      'isPriority' => $this->isIsPriority(),
      'assignedUsers' => $this->getJsonAssignedUsers(),
      'pdfs' => $this->getJsonPdfs(),
      'minEntry' => $this->getMinEntry(),
      'roundingInterval' => $this->getRoundingInterval(),
      'deadline' => $this->getDeadline()
    ];
  }

  public function getEditByJson(): string {
    if(is_null($this->editBy)) {
      return '';
    }
    return $this->editBy->getFullName();
  }

  public function getLabelJson(): array {
    $labels = [];
    foreach ($this->label as $lab) {
      $labels[] = $lab->getTitle();
    }

    return $labels;
  }

  public function getJsonAssignedUsers(): array {
    $users = [];
    foreach ($this->assignedUsers as $user) {
      $users[] = $user->getNameForForm();
    }

    return $users;
  }

  public function getJsonPdfs(): array {
    $pdfs = [];
    foreach ($this->pdfs as $pdf) {
      $pdfs[] = $pdf->getTitle();
    }

    return $pdfs;
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

  /**
   * @return bool|null
   */
  public function isIsPriority(): ?bool {
    return $this->isPriority;
  }

  /**
   * @param bool|null $isPriority
   */
  public function setIsPriority(?bool $isPriority): void {
    $this->isPriority = $isPriority;
  }


  public function isIsEstimate(): ?bool {
    return $this->isEstimate;
  }

  public function setIsEstimate(bool $isEstimate): self {
    $this->isEstimate = $isEstimate;

    return $this;
  }

  public function isIsClosed(): ?bool {
    return $this->isClosed;
  }

  public function setIsClosed(bool $isClosed): self {
    $this->isClosed = $isClosed;

    return $this;
  }

  public function isIsDeleted(): ?bool {
    return $this->isDeleted;
  }

  public function setIsDeleted(bool $isDeleted): self {
    $this->isDeleted = $isDeleted;

    return $this;
  }

  public function isIsExpenses(): ?bool {
    return $this->isExpenses;
  }

  public function setIsExpenses(bool $isExpenses): self {
    $this->isExpenses = $isExpenses;

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

  public function getProject(): ?Project {
    return $this->project;
  }

  public function setProject(?Project $project): self {
    $this->project = $project;

    return $this;
  }

  /**
   * @return Collection<int, User>
   */
  public function getAssignedUsers(): Collection {
    return $this->assignedUsers;
  }

  public function addAssignedUser(User $assignedUser): self {
    if (!$this->assignedUsers->contains($assignedUser)) {
      $this->assignedUsers->add($assignedUser);
    }

    return $this;
  }

  public function removeAssignedUser(User $assignedUser): self {
    $this->assignedUsers->removeElement($assignedUser);

    return $this;
  }

  public function getParentTask(): ?self {
    return $this->parentTask;
  }
  public function getParentTaskJson(): string {
    if(is_null($this->parentTask)) {
      return '';
    }
    return $this->parentTask->getTitle();
  }
  public function setParentTask(?self $parentTask): self {
    $this->parentTask = $parentTask;

    return $this;
  }

  /**
   * @return Collection<int, TaskHistory>
   */
  public function getTaskHistories(): Collection {
    return $this->taskHistories;
  }

  public function addTaskHistory(TaskHistory $taskHistory): self {
    if (!$this->taskHistories->contains($taskHistory)) {
      $this->taskHistories->add($taskHistory);
      $taskHistory->setTask($this);
    }

    return $this;
  }

  public function removeTaskHistory(TaskHistory $taskHistory): self {
    if ($this->taskHistories->removeElement($taskHistory)) {
      // set the owning side to null (unless already changed)
      if ($taskHistory->getTask() === $this) {
        $taskHistory->setTask(null);
      }
    }

    return $this;
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
      $pdf->setTask($this);
    }

    return $this;
  }

  public function removePdf(Pdf $pdf): self {
    if ($this->pdfs->removeElement($pdf)) {
      // set the owning side to null (unless already changed)
      if ($pdf->getTask() === $this) {
        $pdf->setTask(null);
      }
    }

    return $this;
  }
  public function getCategoryJson(): ?string {
    if (is_null($this->category)) {
      return null;
    }
    return $this->category->getTitle();
  }

  public function getProjectJson(): ?string {
    if (is_null($this->project)) {
      return null;
    }
    return $this->project->getTitle();
  }

  public function getCategory(): ?Category {
    return $this->category;
  }

  public function setCategory(?Category $category): self {
    $this->category = $category;

    return $this;
  }

  /**
   * @return Collection<int, Label>
   */
  public function getLabel(): Collection {
    return $this->label;
  }

  public function addLabel(Label $label): self {
    if (!$this->label->contains($label)) {
      $this->label->add($label);
    }

    return $this;
  }

  public function removeLabel(Label $label): self {
    $this->label->removeElement($label);

    return $this;
  }

  /**
   * @return Collection<int, TaskLog>
   */
  public function getTaskLogs(): Collection
  {
      return $this->taskLogs;
  }

  public function addTaskLog(TaskLog $taskLog): self
  {
      if (!$this->taskLogs->contains($taskLog)) {
          $this->taskLogs->add($taskLog);
          $taskLog->setTask($this);
      }

      return $this;
  }

  public function removeTaskLog(TaskLog $taskLog): self
  {
      if ($this->taskLogs->removeElement($taskLog)) {
          // set the owning side to null (unless already changed)
          if ($taskLog->getTask() === $this) {
              $taskLog->setTask(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, Comment>
   */
  public function getComments(): Collection
  {
      return $this->comments;
  }

  public function addComment(Comment $comment): self
  {
      if (!$this->comments->contains($comment)) {
          $this->comments->add($comment);
          $comment->setTask($this);
      }

      return $this;
  }

  public function removeComment(Comment $comment): self
  {
      if ($this->comments->removeElement($comment)) {
          // set the owning side to null (unless already changed)
          if ($comment->getTask() === $this) {
              $comment->setTask(null);
          }
      }

      return $this;
  }

}
