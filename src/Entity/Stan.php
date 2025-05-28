<?php

namespace App\Entity;

use App\Repository\StanRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Stan {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $stanje = null;

    #[ORM\Column(nullable: true)]
    private ?float $percent = null;


    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\ManyToOne(inversedBy: 'stans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sprat $sprat = null;

    #[ORM\ManyToOne(inversedBy: 'stans')]
    private ?Image $image = null;

    #[ORM\OneToMany(mappedBy: 'stan', targetEntity: Prostorija::class, cascade: ['persist'])]
    private Collection $prostorijas;

    public function __construct()
    {
        $this->prostorijas = new ArrayCollection();
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

    public function getSprat(): ?Sprat {
        return $this->sprat;
    }

    public function setSprat(?Sprat $sprat): self {
        $this->sprat = $sprat;

        return $this;
    }

    public function getImage(): ?Image {
        return $this->image;
    }

    public function setImage(?Image $image): self {
        $this->image = $image;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getStanje(): ?string {
        return $this->stanje;
    }

    public function setStanje(?string $stanje): void {
        $this->stanje = $stanje;
    }

    public function getPercent(): ?float {
        return $this->percent;
    }

    public function setPercent(?float $percent): void {
        $this->percent = $percent;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
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
     * @return Collection<int, Prostorija>
     */
    public function getProstorijas(): Collection
    {
        return $this->prostorijas;
    }

    public function addProstorija(Prostorija $prostorija): self
    {
        if (!$this->prostorijas->contains($prostorija)) {
            $this->prostorijas->add($prostorija);
            $prostorija->setStan($this);
        }

        return $this;
    }

    public function removeProstorija(Prostorija $prostorija): self
    {
        if ($this->prostorijas->removeElement($prostorija)) {
            // set the owning side to null (unless already changed)
            if ($prostorija->getStan() === $this) {
                $prostorija->setStan(null);
            }
        }

        return $this;
    }

}
