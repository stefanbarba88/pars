<?php

namespace App\Entity;

use App\Repository\TitulaRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TitulaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['title', 'company'], message: 'U bazi već postoji zvanje sa ovim nazivom.')]
class Titula {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
  }

  #[ORM\OneToMany(mappedBy: 'zvanje', targetEntity: User::class)]
  private Collection $users;

  public function __construct() {
    $this->users = new ArrayCollection();
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
  }

  public function getId(): ?int {
    return $this->id;
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
   * @return Collection<int, User>
   */
  public function getUsers(): Collection {
    return $this->users;
  }

  public function addUser(User $user): self {
    if (!$this->users->contains($user)) {
      $this->users->add($user);
      $user->setZvanje($this);
    }

    return $this;
  }

  public function removeUser(User $user): self {
    if ($this->users->removeElement($user)) {
      // set the owning side to null (unless already changed)
      if ($user->getZvanje() === $this) {
        $user->setZvanje(null);
      }
    }

    return $this;
  }

  public function getForForm(): string {
    return '(' . $this->id . ') ' . ($this->title);
  }

}