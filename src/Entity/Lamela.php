<?php

namespace App\Entity;

use App\Classes\Data\StatusElaboratData;
use App\Classes\Data\TipProstoraData;
use App\Repository\LamelaRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LamelaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Lamela {
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

    #[ORM\Column(nullable: true)]
    private ?int $prostor = TipProstoraData::STAMBENI;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $deadline = null;

    #[ORM\ManyToOne(inversedBy: 'lamelas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Projekat $projekat = null;

    #[ORM\OneToMany(mappedBy: 'lamela', targetEntity: Sprat::class)]
    private Collection $sprats;

    public function __construct()
    {
        $this->sprats = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjekat(): ?Projekat
    {
        return $this->projekat;
    }

    public function setProjekat(?Projekat $projekat): self
    {
        $this->projekat = $projekat;

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

    public function getDeadline(): ?DateTimeImmutable {
        return $this->deadline;
    }

    public function setDeadline(?DateTimeImmutable $deadline): void {
        $this->deadline = $deadline;
    }

    public function getProstor(): ?int {
        return $this->prostor;
    }

    public function setProstor(?int $prostor): void {
        $this->prostor = $prostor;
    }

    /**
     * @return Collection<int, Sprat>
     */
    public function getSprats(): Collection
    {
        return $this->sprats;
    }

    public function addSprat(Sprat $sprat): self
    {
        if (!$this->sprats->contains($sprat)) {
            $this->sprats->add($sprat);
            $sprat->setLamela($this);
        }

        return $this;
    }

    public function removeSprat(Sprat $sprat): self
    {
        if ($this->sprats->removeElement($sprat)) {
            // set the owning side to null (unless already changed)
            if ($sprat->getLamela() === $this) {
                $sprat->setLamela(null);
            }
        }

        return $this;
    }

}
