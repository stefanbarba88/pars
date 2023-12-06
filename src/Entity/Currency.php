<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'currencies')]
class Currency {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255)]
  private ?string $short = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $symbol = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'currency', targetEntity: Project::class)]
  private Collection $projects;

  public function __construct() {
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
  public function getSymbol(): ?string {
    return $this->symbol;
  }

  /**
   * @param string|null $symbol
   */
  public function setSymbol(?string $symbol): void {
    $this->symbol = $symbol;
  }

  /**
   * @return string|null
   */
  public function getShort(): ?string {
    return $this->short;
  }

  /**
   * @param string|null $short
   */
  public function setShort(?string $short): void {
    $this->short = $short;
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
      $project->setCurrency($this);
    }

    return $this;
  }

  public function removeProject(Project $project): self {
    if ($this->projects->removeElement($project)) {
      // set the owning side to null (unless already changed)
      if ($project->getCurrency() === $this) {
        $project->setCurrency(null);
      }
    }

    return $this;
  }

  public function getFormTitle(): ?string {
    if (is_null($this->symbol)) {
      return $this->short . ' - ' . $this->title;
    } else {
      return $this->short . ' (' . $this->symbol . ') - ' . $this->title;
    }
  }


}
