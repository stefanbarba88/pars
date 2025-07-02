<?php

namespace App\Entity;

use App\Repository\SpratRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpratRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Sprat {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $stanje = null;

    #[ORM\Column(nullable: true)]
    private ?float $povrsina = null;

    #[ORM\Column(nullable: true)]
    private ?float $percent = null;


    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $deadline = null;

    #[ORM\ManyToOne(inversedBy: 'sprats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lamela $lamela = null;

    #[ORM\OneToMany(mappedBy: 'sprat', targetEntity: Stan::class)]
    private Collection $stans;

    public function __construct()
    {
        $this->stans = new ArrayCollection();
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

    public function getLamela(): ?Lamela {
        return $this->lamela;
    }

    public function setLamela(?Lamela $lamela): self {
        $this->lamela = $lamela;

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

    /**
     * @return Collection<int, Stan>
     */
    public function getStans(): Collection
    {
        return $this->stans;
    }

    public function addStan(Stan $stan): self
    {
        if (!$this->stans->contains($stan)) {
            $this->stans->add($stan);
            $stan->setSprat($this);
        }

        return $this;
    }

    public function removeStan(Stan $stan): self
    {
        if ($this->stans->removeElement($stan)) {
            // set the owning side to null (unless already changed)
            if ($stan->getSprat() === $this) {
                $stan->setSprat(null);
            }
        }

        return $this;
    }

    public function getPovrsina(): ?float {
        return $this->povrsina;
    }

    public function setPovrsina(?float $povrsina): void {
        $this->povrsina = $povrsina;
    }

}
