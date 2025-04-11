<?php

namespace App\Entity;

use App\Repository\SastavnicaRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SastavnicaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Sastavnica {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 3, nullable: true)]
    private ?string $sastav = null;
    #[ORM\ManyToOne(inversedBy: 'sastavnicas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Element $element = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\ManyToOne(inversedBy: 'sastavnica')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

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

    public function getElement(): ?Element {
        return $this->element;
    }

    public function setElement(?Element $element): static {
        $this->element = $element;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getSastav(): ?string {
        return $this->sastav;
    }

    public function setSastav(?string $sastav): void {
        $this->sastav = $sastav;
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
}
