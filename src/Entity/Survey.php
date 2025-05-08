<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SurveyRepository::class)]
class Survey {

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isActive = true;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Vote::class, orphanRemoval: true)]
  private Collection $vote;

  public function __construct() {
    $this->vote = new ArrayCollection();
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

  /**
   * @return Collection<int, Vote>
   */
  public function getVote(): Collection {
    return $this->vote;
  }

  public function addVote(Vote $vote): self {
    if (!$this->vote->contains($vote)) {
      $this->vote->add($vote);
      $vote->setSurvey($this);
    }

    return $this;
  }

  public function removeVote(Vote $vote): self {
    if ($this->vote->removeElement($vote)) {
      // set the owning side to null (unless already changed)
      if ($vote->getSurvey() === $this) {
        $vote->setSurvey(null);
      }
    }

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
   * @return bool|null
   */
  public function getIsActive(): ?bool {
    return $this->isActive;
  }

  /**
   * @param bool|null $isActive
   */
  public function setIsActive(?bool $isActive): void {
    $this->isActive = $isActive;
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

}
