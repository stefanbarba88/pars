<?php

namespace App\Entity;

use App\Repository\TaskLogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskLogRepository::class)]
class TaskLog {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\OneToMany(mappedBy: 'taskLog', targetEntity: StopwatchTime::class, orphanRemoval: true)]
  private Collection $stopwatchTimes;

  #[ORM\ManyToMany(targetEntity: Activity::class)]
  private Collection $activity;

  #[ORM\ManyToOne(inversedBy: 'taskLogs')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\OneToMany(mappedBy: 'TaskLog', targetEntity: Pdf::class)]
  private Collection $pdfs;

  public function __construct() {
    $this->stopwatchTimes = new ArrayCollection();
    $this->activity = new ArrayCollection();
    $this->pdfs = new ArrayCollection();
  }

  public function getId(): ?int {
    return $this->id;
  }

  /**
   * @return Collection<int, StopwatchTime>
   */
  public function getStopwatchTimes(): Collection {
    return $this->stopwatchTimes;
  }

  public function addStopwatchTime(StopwatchTime $stopwatchTime): self {
    if (!$this->stopwatchTimes->contains($stopwatchTime)) {
      $this->stopwatchTimes->add($stopwatchTime);
      $stopwatchTime->setTaskLog($this);
    }

    return $this;
  }

  public function removeStopwatchTime(StopwatchTime $stopwatchTime): self {
    if ($this->stopwatchTimes->removeElement($stopwatchTime)) {
      // set the owning side to null (unless already changed)
      if ($stopwatchTime->getTaskLog() === $this) {
        $stopwatchTime->setTaskLog(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Activity>
   */
  public function getActivity(): Collection
  {
      return $this->activity;
  }

  public function addActivity(Activity $activity): self
  {
      if (!$this->activity->contains($activity)) {
          $this->activity->add($activity);
      }

      return $this;
  }

  public function removeActivity(Activity $activity): self
  {
      $this->activity->removeElement($activity);

      return $this;
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
   * @return Collection<int, Pdf>
   */
  public function getPdfs(): Collection
  {
      return $this->pdfs;
  }

  public function addPdf(Pdf $pdf): self
  {
      if (!$this->pdfs->contains($pdf)) {
          $this->pdfs->add($pdf);
          $pdf->setTaskLog($this);
      }

      return $this;
  }

  public function removePdf(Pdf $pdf): self
  {
      if ($this->pdfs->removeElement($pdf)) {
          // set the owning side to null (unless already changed)
          if ($pdf->getTaskLog() === $this) {
              $pdf->setTaskLog(null);
          }
      }

      return $this;
  }
}
