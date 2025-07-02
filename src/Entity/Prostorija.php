<?php

namespace App\Entity;

use App\Repository\ProstorijaRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProstorijaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Prostorija {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?float $povrs = null;

    #[ORM\Column(nullable: true)]
    private ?string $odstupanje = null;

    #[ORM\Column(nullable: true)]
    private ?float $povrsina = null;

    #[ORM\Column(nullable: true)]
    private ?float $dijagonala1 = null;
    #[ORM\Column(nullable: true)]
    private ?float $dijagonala2 = null;

    #[ORM\Column(nullable: true)]
    private ?float $dijagonala3 = null;
    #[ORM\Column(nullable: true)]
    private ?float $dijagonala4 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $unos1 = null;
    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $unos2 = null;
    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $unos3 = null;

    #[ORM\Column]
    private bool $isEdit = false;

    #[ORM\Column]
    private bool $isCustom = false;

    #[ORM\Column]
    private bool $isRepeat = false;

    #[ORM\Column]
    private bool $isPlan = false;

    #[ORM\Column]
    private bool $isEditManual = false;

    #[ORM\Column]
    private bool $isEditAuto = false;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $archive = null;


    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $description1 = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\PrePersist]
    public function prePersist(): void {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void {
        $this->updated = new DateTimeImmutable();
    }

    #[ORM\ManyToOne(inversedBy: 'prostorijas')]
    private ?Stan $stan = null;

    #[ORM\ManyToOne(inversedBy: 'prostorijas')]
    private ?Sifarnik $code = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getStan(): ?Stan {
        return $this->stan;
    }

    public function setStan(?Stan $stan): self {
        $this->stan = $stan;

        return $this;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getOdstupanje(): ?string {
        return $this->odstupanje;
    }

    public function setOdstupanje(?string $odstupanje): void {
        $this->odstupanje = $odstupanje;
    }

    public function getPovrsina(): ?float {
        return $this->povrsina;
    }

    public function setPovrsina(?float $povrsina): void {
        $this->povrsina = $povrsina;
    }

    /**
     * @return array|null
     */
    public function getUnos1(): ?array {
        return json_decode($this->unos1, true);
    }

    /**
     * @param array|null $unos1
     */
    public function setUnos1(?array $unos1): self {
        $this->unos1 = json_encode($unos1);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getUnos2(): ?array {
        return json_decode($this->unos2, true);
    }

    /**
     * @param array|null $unos2
     */
    public function setUnos2(?array $unos2): self {
        $this->unos2 = json_encode($unos2);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getUnos3(): ?array {
        return json_decode($this->unos3, true);
    }

    /**
     * @param array|null $unos3
     */
    public function setUnos3(?array $unos3): self {
        $this->unos3 = json_encode($unos3);
        return $this;
    }



    public function isEdit(): bool {
        return $this->isEdit;
    }

    public function setIsEdit(bool $isEdit): void {
        $this->isEdit = $isEdit;
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

    public function getCode(): ?Sifarnik {
        return $this->code;
    }

    public function setCode(?Sifarnik $code): self {
        $this->code = $code;

        return $this;
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

    public function isEditManual(): bool {
        return $this->isEditManual;
    }

    public function setIsEditManual(bool $isEditManual): void {
        $this->isEditManual = $isEditManual;
    }

    public function isEditAuto(): bool {
        return $this->isEditAuto;
    }

    public function setIsEditAuto(bool $isEditAuto): void {
        $this->isEditAuto = $isEditAuto;
    }

    public function getDijagonala1(): ?float {
        return $this->dijagonala1;
    }

    public function setDijagonala1(?float $dijagonala1): void {
        $this->dijagonala1 = $dijagonala1;
    }

    public function getDijagonala2(): ?float {
        return $this->dijagonala2;
    }

    public function setDijagonala2(?float $dijagonala2): void {
        $this->dijagonala2 = $dijagonala2;
    }

    public function getDescription1(): ?string {
        return $this->description1;
    }

    public function setDescription1(?string $description1): void {
        $this->description1 = $description1;
    }

    public function isCustom(): bool {
        return $this->isCustom;
    }

    public function setIsCustom(bool $isCustom): void {
        $this->isCustom = $isCustom;
    }

    public function getDijagonala3(): ?float {
        return $this->dijagonala3;
    }

    public function setDijagonala3(?float $dijagonala3): void {
        $this->dijagonala3 = $dijagonala3;
    }

    public function getDijagonala4(): ?float {
        return $this->dijagonala4;
    }

    public function setDijagonala4(?float $dijagonala4): void {
        $this->dijagonala4 = $dijagonala4;
    }

    public function getPovrs(): ?float {
        return $this->povrs;
    }

    public function setPovrs(?float $povrs): void {
        $this->povrs = $povrs;
    }

    public function isRepeat(): bool {
        return $this->isRepeat;
    }

    public function setIsRepeat(bool $isRepeat): void {
        $this->isRepeat = $isRepeat;
    }

    public function isPlan(): bool {
        return $this->isPlan;
    }

    public function setIsPlan(bool $isPlan): void {
        $this->isPlan = $isPlan;
    }


}
