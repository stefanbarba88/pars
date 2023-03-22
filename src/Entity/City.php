<?php

namespace App\Entity;

use App\Repository\CityRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['ptt', 'drzava'], message: 'U bazi već postoji grad sa ovim PTT brojem.')]
#[ORM\Table(name: 'cities')]
class City {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column]
  private ?string $ptt = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $region = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $municipality = null;

  #[ORM\ManyToOne]
  private ?Country $drzava = null;

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

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
  }


  public function getFormTitle(): ?string {
    return $this->title . ' (' . $this->ptt . ')';
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

  public function getPtt(): ?string {
    return $this->ptt;
  }

  public function setPtt(string $ptt): self {
    $this->ptt = $ptt;

    return $this;
  }

  public function getRegion(): ?string {
    return $this->region;
  }

  public function setRegion(string $region): self {
    $this->region = $region;

    return $this;
  }

  public function getMunicipality(): ?string {
    return $this->municipality;
  }

  public function setMunicipality(string $municipality): self {
    $this->municipality = $municipality;

    return $this;
  }

  public function getDrzava(): ?Country {
    return $this->drzava;
  }

  public function setDrzava(?Country $drzava): self {
    $this->drzava = $drzava;

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




}
