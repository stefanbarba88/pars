<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'teams')]
class Team {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column]
  private ?bool $isDeleted = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'teams')]
  private Collection $member;

  #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'team')]
  private Collection $projects;

  public function __construct()
  {
      $this->member = new ArrayCollection();
      $this->projects = new ArrayCollection();
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
   * @return bool|null
   */
  public function getIsDeleted(): ?bool {
    return $this->isDeleted;
  }

  /**
   * @param bool|null $isDeleted
   */
  public function setIsDeleted(?bool $isDeleted): void {
    $this->isDeleted = $isDeleted;
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
   * @return Collection<int, User>
   */
  public function getMember(): Collection
  {
      return $this->member;
  }

  public function addMember(User $member): self
  {
      if (!$this->member->contains($member)) {
          $this->member->add($member);
      }

      return $this;
  }

  public function removeMember(User $member): self
  {
      $this->member->removeElement($member);

      return $this;
  }

  /**
   * @return Collection<int, Project>
   */
  public function getProjects(): Collection
  {
      return $this->projects;
  }

  public function addProject(Project $project): self
  {
      if (!$this->projects->contains($project)) {
          $this->projects->add($project);
          $project->addTeam($this);
      }

      return $this;
  }

  public function removeProject(Project $project): self
  {
      if ($this->projects->removeElement($project)) {
          $project->removeTeam($this);
      }

      return $this;
  }




}
