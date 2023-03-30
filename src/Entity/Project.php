<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'projects')]
class Project {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $description = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'projects')]
  private Collection $category;

  #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'projects')]
  private Collection $client;

  #[ORM\ManyToOne(inversedBy: 'projects')]
  private ?Label $label = null;

  public function __construct() {
    $this->category = new ArrayCollection();
    $this->client = new ArrayCollection();
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
   * @return Collection<int, Category>
   */
  public function getCategory(): Collection {
    return $this->category;
  }

  public function addCategory(Category $category): self {
    if (!$this->category->contains($category)) {
      $this->category->add($category);
    }

    return $this;
  }

  public function removeCategory(Category $category): self {
    $this->category->removeElement($category);

    return $this;
  }

  /**
   * @return Collection<int, Client>
   */
  public function getClient(): Collection {
    return $this->client;
  }

  public function addClient(Client $client): self {
    if (!$this->client->contains($client)) {
      $this->client->add($client);
    }

    return $this;
  }

  public function removeClient(Client $client): self {
    $this->client->removeElement($client);

    return $this;
  }

  public function getLabel(): ?Label {
    return $this->label;
  }

  public function setLabel(?Label $label): self {
    $this->label = $label;

    return $this;
  }
}
