<?php

namespace App\Entity;

use App\Classes\Slugify;
use App\Repository\TaskLogRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TaskLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TaskLog implements JsonSerializable {


  public function getUploadPath(): ?string {
    return $this->getTask()->getUploadPath() . date('Y/m/d/');
  }

  public function getThumbUploadPath(): ?string {
    return $this->getTask()->getThumbUploadPath() . date('Y/m/d/');
  }

  public function getNoProjectUploadPath(): ?string {
    return $this->getNoProjectUploadPath() . date('Y/m/d/');
  }

  public function getNoProjectThumbUploadPath(): ?string {
    return $this->getNoProjectThumbUploadPath() . date('Y/m/d/');
  }


  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $user = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne(inversedBy: 'taskLogs')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Task $task = null;

  #[ORM\OneToMany(mappedBy: 'taskLog', targetEntity: StopwatchTime::class, orphanRemoval: true)]
  private Collection $stopwatch;

  public function __construct() {
    $this->stopwatch = new ArrayCollection();
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

  public function jsonSerialize(): array {
    return [
      'id' => $this->getId(),
      'task' => $this->getTask()->getTitle(),
      'description' => $this->getDescription(),
      'isSuspended' => $this->isSuspended(),
      'editBy' => $this->getEditByJson(),
      'user' => $this->getUser()->getFullName(),
    ];
  }

//  public function getJsonPdfs(): array {
//    $pdfs = [];
//    foreach ($this->pdf as $pdf) {
//      $pdfs[] = $pdf->getTitle();
//    }
//
//    return $pdfs;
//  }
//
//  public function getJsonImages(): array {
//    $images = [];
//    foreach ($this->image as $image) {
//      $images[] = $image->getThumbnail100();
//    }
//
//    return $images;
//  }
//
//  public function getJsonActivities(): array {
//    $activities = [];
//    foreach ($this->activity as $activity) {
//      $activities[] = $activity->getTitle();
//    }
//
//    return $activities;
//  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
  }

  /**
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  public function getEditByJson(): string {
    if (is_null($this->editBy)) {
      return '';
    }
    return $this->editBy->getFullName();
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
  public function getUser(): ?User {
    return $this->user;
  }

  /**
   * @param User|null $createdBy
   */
  public function setUser(?User $user): void {
    $this->user = $user;
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

  public function getTask(): ?Task
  {
      return $this->task;
  }

  public function setTask(?Task $task): self
  {
      $this->task = $task;

      return $this;
  }

  /**
   * @return Collection<int, StopwatchTime>
   */
  public function getStopwatch(): Collection
  {
      return $this->stopwatch;
  }

  public function addStopwatch(StopwatchTime $stopwatch): self
  {
      if (!$this->stopwatch->contains($stopwatch)) {
          $this->stopwatch->add($stopwatch);
          $stopwatch->setTaskLog($this);
      }

      return $this;
  }

  public function removeStopwatch(StopwatchTime $stopwatch): self
  {
      if ($this->stopwatch->removeElement($stopwatch)) {
          // set the owning side to null (unless already changed)
          if ($stopwatch->getTaskLog() === $this) {
              $stopwatch->setTaskLog(null);
          }
      }

      return $this;
  }

}