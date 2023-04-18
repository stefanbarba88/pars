<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'categories')]
class Category {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isTaskCategory = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'category')]
  private Collection $projects;

  #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'category')]
  private Collection $tasks;

  public function __construct() {
    $this->projects = new ArrayCollection();
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

  public function getBadgeByStatus(): string {
    if ($this->isSuspended) {
      return '<span class="badge bg-danger">Neaktivan</span>';
    }
    return '<span class="badge bg-info">Aktivan</span>';

  }

  /**
   * @return Collection<int, Project>
   */
  public function getProjects(): Collection {
    return $this->projects;
  }

  public function addProject(Project $project): self {
    if (!$this->projects->contains($project)) {
      $this->projects->add($project);
      $project->addCategory($this);
    }

    return $this;
  }

  public function removeProject(Project $project): self {
    if ($this->projects->removeElement($project)) {
      $project->removeCategory($this);
    }

    return $this;
  }

  /**
   * @return bool
   */
  public function isTaskCategory(): bool {
    return $this->isTaskCategory;
  }

  /**
   * @param bool $isTaskCategory
   */
  public function setIsTaskCategory(bool $isTaskCategory): void {
    $this->isTaskCategory = $isTaskCategory;
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
          $task->addCategory($this);
      }

      return $this;
  }

  public function removeTask(Task $task): self
  {
      if ($this->tasks->removeElement($task)) {
          $task->removeCategory($this);
      }

      return $this;
  }


}
