<?php

namespace App\Entity;

use App\Repository\SifarnikRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SifarnikRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Sifarnik {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titleShort = null;

    #[ORM\Column(nullable: true)]
    private ?float $struktura = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\OneToMany(mappedBy: 'code', targetEntity: Prostorija::class)]
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

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
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
            $prostorija->setCode($this);
        }

        return $this;
    }

    public function removeProstorija(Prostorija $prostorija): self
    {
        if ($this->prostorijas->removeElement($prostorija)) {
            // set the owning side to null (unless already changed)
            if ($prostorija->getCode() === $this) {
                $prostorija->setCode(null);
            }
        }

        return $this;
    }

    public function getTitleShort(): ?string {
        return $this->titleShort;
    }

    public function setTitleShort(?string $titleShort): void {
        $this->titleShort = $titleShort;
    }

    public function getStruktura(): ?float {
        return $this->struktura;
    }

    public function setStruktura(?float $struktura): void {
        $this->struktura = $struktura;
    }

}
