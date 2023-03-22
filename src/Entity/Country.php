<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'countries')]
class Country {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $titleEn = null;

  #[ORM\Column(length: 2, nullable: true)]
  private ?string $short2 = null;

  #[ORM\Column(length: 3, nullable: true)]
  private ?string $short3 = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $num = null;

  public function getFormTitle(): ?string {
    return $this->title . ' (' . $this->short3 . ')';
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

  public function getTitleEn(): ?string {
    return $this->titleEn;
  }

  public function setTitleEn(?string $titleEn): self {
    $this->titleEn = $titleEn;

    return $this;
  }

  public function getShort2(): ?string {
    return $this->short2;
  }

  public function setShort2(?string $short2): self {
    $this->short2 = $short2;

    return $this;
  }

  public function getShort3(): ?string {
    return $this->short3;
  }

  public function setShort3(?string $short3): self {
    $this->short3 = $short3;

    return $this;
  }

  public function getNum(): ?string {
    return $this->num;
  }

  public function setNum(?string $num): self {
    $this->num = $num;

    return $this;
  }
}
