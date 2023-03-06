<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ORM\Table(name: 'cities')]
class City {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column]
  private ?int $ptt = null;

  #[ORM\Column(length: 255)]
  private ?string $region = null;

  #[ORM\Column(length: 255)]
  private ?string $municipality = null;

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

  public function getPtt(): ?int {
    return $this->ptt;
  }

  public function setPtt(int $ptt): self {
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
}
