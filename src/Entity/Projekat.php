<?php

namespace App\Entity;

use App\Classes\Slugify;
use App\Repository\ProjekatRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjekatRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Projekat {

    public function getUploadPath(): ?string {
        return $_ENV['PROJEKAT_IMAGE_PATH'] . Slugify::slugify($this->title) . '/';
    }

    public function getThumbUploadPath(): ?string {
        return $_ENV['PROJEKAT_THUMB_PATH'] . Slugify::slugify($this->title) . '/';
    }

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
    private ?float $povrsina = null;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description = null;



    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $deadline = null;

    #[ORM\ManyToOne(inversedBy: 'projekats')]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'projekat', targetEntity: Lamela::class)]
    private Collection $lamelas;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'projekats')]
    private Collection $assigned;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $zaposleni = null;

    public function __construct()
    {
        $this->lamelas = new ArrayCollection();
        $this->assigned = new ArrayCollection();

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

    /**
     * @return array|null
     */
    public function getZaposleni(): ?array {
        return json_decode($this->zaposleni, true);
    }

    /**
     * @param array|null $zaposleni
     */
    public function setZaposleni(?array $zaposleni): self {
        $this->zaposleni = json_encode($zaposleni);
        return $this;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getClient(): ?Client {
        return $this->client;
    }

    public function setClient(?Client $client): self {
        $this->client = $client;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
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

    /**
     * @return Collection<int, Lamela>
     */
    public function getLamelas(): Collection
    {
        return $this->lamelas;
    }

    public function addLamela(Lamela $lamela): self
    {
        if (!$this->lamelas->contains($lamela)) {
            $this->lamelas->add($lamela);
            $lamela->setProjekat($this);
        }

        return $this;
    }

    public function removeLamela(Lamela $lamela): self
    {
        if ($this->lamelas->removeElement($lamela)) {
            // set the owning side to null (unless already changed)
            if ($lamela->getProjekat() === $this) {
                $lamela->setProjekat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAssigned(): Collection
    {
        return $this->assigned;
    }

    public function addAssigned(User $assigned): self
    {
        if (!$this->assigned->contains($assigned)) {
            $this->assigned->add($assigned);
        }

        return $this;
    }

    public function removeAssigned(User $assigned): self
    {
        $this->assigned->removeElement($assigned);

        return $this;
    }

    public function getPovrsina(): ?float {
        return $this->povrsina;
    }

    public function setPovrsina(?float $povrsina): void {
        $this->povrsina = $povrsina;
    }



}
