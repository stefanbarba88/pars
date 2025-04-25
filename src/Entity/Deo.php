<?php

namespace App\Entity;

use App\Repository\DeoRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeoRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Deo {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $archive = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $kolicina = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\ManyToOne(inversedBy: 'deo')]
    private ?Production $production = null;





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

    public function getProduct(): ?Product {
        return $this->product;
    }

    public function setProduct(?Product $product): static {
        $this->product = $product;

        return $this;
    }

    public function getKolicina(): ?string {
        return $this->kolicina;
    }

    public function setKolicina(?string $kolicina): void {
        $this->kolicina = $kolicina;
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
     * @return array|null
     */
    public function getArchive(): ?array {
        return json_decode($this->archive, true);
    }

    /**
     * @param array|null $archive
     */
    public function setArchive(?array $archive): self {
        $this->archive = json_encode($archive);
        return $this;
    }

    public function getProduction(): ?Production
    {
        return $this->production;
    }

    public function setProduction(?Production $production): static
    {
        $this->production = $production;

        return $this;
    }
}
