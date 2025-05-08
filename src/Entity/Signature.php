<?php

namespace App\Entity;

use App\Repository\SignatureRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Signature {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'signatures')]
  private ?Project $relation = null;

  #[ORM\ManyToOne(inversedBy: 'signatures')]
  private ?User $employee = null;

  #[ORM\Column]
  private bool $isApproved = true;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToOne(cascade: ['persist', 'remove'])]
  private ?Image $image = null;

//  #[ORM\OneToMany(mappedBy: 'signature', targetEntity: Image::class, cascade: ["persist", "remove"])]
//  private Collection $image;

//  public function __construct() {
//    $this->image = new ArrayCollection();
//  }

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

  public function getRelation(): ?Project {
    return $this->relation;
  }

  public function setRelation(?Project $relation): self {
    $this->relation = $relation;

    return $this;
  }

  public function getEmployee(): ?User {
    return $this->employee;
  }

  public function setEmployee(?User $employee): self {
    $this->employee = $employee;

    return $this;
  }

//  /**
//   * @return Collection<int, Image>
//   */
//  public function getImage(): Collection {
//    return $this->image;
//  }
//
//  public function addImage(Image $image): self {
//    if (!$this->image->contains($image)) {
//      $this->image->add($image);
//      $image->setSignature($this);
//    }
//
//    return $this;
//  }
//
//  public function removeImage(Image $image): self {
//    if ($this->image->removeElement($image)) {
//      // set the owning side to null (unless already changed)
//      if ($image->getSignature() === $this) {
//        $image->setSignature(null);
//      }
//    }
//
//    return $this;
//  }

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
  public function isApproved(): bool {
    return $this->isApproved;
  }

  /**
   * @param bool $isApproved
   */
  public function setIsApproved(bool $isApproved): void {
    $this->isApproved = $isApproved;
  }

  public function getImage(): ?Image
  {
      return $this->image;
  }

  public function setImage(?Image $image): self
  {
      $this->image = $image;

      return $this;
  }


}
