<?php

namespace App\Entity;

use App\Repository\LabelRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LabelRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'labels')]
class Label {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255)]
  private ?string $color = null;

  #[ORM\Column(length: 255)]
  private ?string $label = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isTaskLabel = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'label')]
  private Collection $projects;

  #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'label')]
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
      return '<span class="badge bg-yellow text-primary">Deaktiviran</span>';
    }
    return '<span class="badge bg-primary text-white">Aktivan</span>';

  }

  /**
   * @return string|null
   */
  public function getColor(): ?string {
    return $this->color;
  }

  /**
   * @param string|null $color
   */
  public function setColor(?string $color): void {
    $this->color = $color;
  }

  /**
   * @return string|null
   */
  public function getLabel(): ?string {
    return $this->label;
  }

  /**
   * @param string|null $label
   */
  public function setLabel(?string $label): void {
    $this->label = $label;
  }


  /**
   * @return bool
   */
  public function isTaskLabel(): bool {
    return $this->isTaskLabel;
  }

  /**
   * @param bool $isTaskLabel
   */
  public function setIsTaskLabel(bool $isTaskLabel): void {
    $this->isTaskLabel = $isTaskLabel;
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
      $project->addLabel($this);
    }

    return $this;
  }

  public function removeProject(Project $project): self {
    if ($this->projects->removeElement($project)) {
      $project->removeLabel($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, Task>
   */
  public function getTasks(): Collection {
    return $this->tasks;
  }

  public function addTask(Task $task): self {
    if (!$this->tasks->contains($task)) {
      $this->tasks->add($task);
      $task->addLabel($this);
    }

    return $this;
  }

  public function removeTask(Task $task): self {
    if ($this->tasks->removeElement($task)) {
      $task->removeLabel($this);
    }

    return $this;
  }


}
