<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $productKey = null;
  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
  private ?string $sastav = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'product', targetEntity: Sastavnica::class, cascade: ['persist'])]
  private Collection $sastavnica;

  #[ORM\OneToMany(mappedBy: 'product', targetEntity: Deo::class)]
  private Collection $deos;

  #[ORM\ManyToOne(inversedBy: 'products')]
  private ?Company $company = null;

  public function __construct()
  {
      $this->sastavnica = new ArrayCollection();
      $this->deos = new ArrayCollection();
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



  public function getProductKey(): ?string {
    return $this->productKey;
  }

  public function setProductKey(?string $productKey): void {
    $this->productKey = $productKey;
  }

  public function getTitle(): ?string {
    return $this->title;
  }

  public function setTitle(?string $title): void {
    $this->title = $title;
  }

  public function getSastav(): ?string {
    return $this->sastav;
  }

  public function setSastav(?string $sastav): void {
    $this->sastav = $sastav;
  }

  public function isSuspended(): bool {
    return $this->isSuspended;
  }

  public function setIsSuspended(bool $isSuspended): void {
    $this->isSuspended = $isSuspended;
  }

  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  public function setCreated(DateTimeImmutable $created): void {
    $this->created = $created;
  }

  public function getUpdated(): DateTimeImmutable {
    return $this->updated;
  }

  public function setUpdated(DateTimeImmutable $updated): void {
    $this->updated = $updated;
  }

  /**
   * @return Collection<int, Sastavnica>
   */
  public function getSastavnica(): Collection
  {
      return $this->sastavnica;
  }

  public function addSastavnica(Sastavnica $sastavnica): static
  {
      if (!$this->sastavnica->contains($sastavnica)) {
          $this->sastavnica->add($sastavnica);
          $sastavnica->setProduct($this);
      }

      return $this;
  }

  public function removeSastavnica(Sastavnica $sastavnica): static
  {
      if ($this->sastavnica->removeElement($sastavnica)) {
          // set the owning side to null (unless already changed)
          if ($sastavnica->getProduct() === $this) {
              $sastavnica->setProduct(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, Deo>
   */
  public function getDeos(): Collection
  {
      return $this->deos;
  }

  public function addDeo(Deo $deo): static
  {
      if (!$this->deos->contains($deo)) {
          $this->deos->add($deo);
          $deo->setProduct($this);
      }

      return $this;
  }

  public function removeDeo(Deo $deo): static
  {
      if ($this->deos->removeElement($deo)) {
          // set the owning side to null (unless already changed)
          if ($deo->getProduct() === $this) {
              $deo->setProduct(null);
          }
      }

      return $this;
  }

  public function getCompany(): ?Company
  {
      return $this->company;
  }

  public function setCompany(?Company $company): static
  {
      $this->company = $company;

      return $this;
  }


}
