<?php

namespace App\Entity;

use App\Classes\Slugify;
use App\Repository\ElementRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ElementRepository::class)]
#[UniqueEntity(fields: ['productKey', 'company'], message: 'U bazi već postoji element sa ovom šifrom.')]
#[ORM\HasLifecycleCallbacks]
class Element {

    public function getUploadPath(): ?string {
        return $_ENV['PROJECT_IMAGE_PATH'] . $this->getCompany()->getId() . '/' . Slugify::slugify($this->title) . '/';
    }

    public function getThumbUploadPath(): ?string {
        return $_ENV['PROJECT_THUMB_PATH'] . $this->getCompany()->getId() . '/' . Slugify::slugify($this->title) . '/';
    }

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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'element', targetEntity: Sastavnica::class)]
    private Collection $sastavnicas;

    public function __construct()
    {
        $this->sastavnicas = new ArrayCollection();
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

    public function isSuspended(): bool {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): void {
        $this->isSuspended = $isSuspended;
    }

    public function getProductKey(): ?string {
        return $this->productKey;
    }

    public function setProductKey(?string $productKey): void {
        $this->productKey = $productKey;
    }

    public function getCompany(): ?Company {
        return $this->company;
    }

    public function setCompany(?Company $company): static {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Sastavnica>
     */
    public function getSastavnicas(): Collection
    {
        return $this->sastavnicas;
    }

    public function addSastavnica(Sastavnica $sastavnica): static
    {
        if (!$this->sastavnicas->contains($sastavnica)) {
            $this->sastavnicas->add($sastavnica);
            $sastavnica->setElement($this);
        }

        return $this;
    }

    public function removeSastavnica(Sastavnica $sastavnica): static
    {
        if ($this->sastavnicas->removeElement($sastavnica)) {
            // set the owning side to null (unless already changed)
            if ($sastavnica->getElement() === $this) {
                $sastavnica->setElement(null);
            }
        }

        return $this;
    }

}
