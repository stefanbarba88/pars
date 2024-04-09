<?php

namespace App\Entity;

use App\Repository\StopwatchTimeRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StopwatchTimeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'stopwatch_times')]
class StopwatchTime {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(nullable: true)]
  private ?DateTimeImmutable $start = null;

  #[ORM\Column(nullable: true)]
  private ?DateTimeImmutable $stop = null;

  #[ORM\Column(nullable: true)]
  private ?int $diff = null;

  #[ORM\Column(nullable: true)]
  private ?int $min = null;

  #[ORM\Column(nullable: true)]
  private ?int $checked = null;

  #[ORM\Column]
  private ?bool $isEdited = false;

  #[ORM\Column]
  private ?bool $isManuallyClosed = false;

  #[ORM\Column]
  private ?bool $isDeleted = false;


  #[ORM\Column(nullable: true)]
  private ?int $diffRounded = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
  private ?string $lon = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
  private ?string $lat = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
  private ?string $lonStop = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
  private ?string $latStop = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $additionalActivity = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $additionalDesc = null;


  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToOne(inversedBy: 'stopwatch')]
  #[ORM\JoinColumn(nullable: false)]
  private ?TaskLog $taskLog = null;

  #[ORM\ManyToMany(targetEntity: Activity::class)]
  private Collection $activity;

  #[ORM\OneToMany(mappedBy: 'stopwatchTime', targetEntity: Pdf::class, cascade: ["persist", "remove"])]
  private Collection $pdf;

  #[ORM\OneToMany(mappedBy: 'stopwatchTime', targetEntity: Image::class, cascade: ["persist", "remove"])]
  private Collection $image;

  #[ORM\ManyToOne]
  private ?User $deletedBy = null;

  #[ORM\ManyToOne]
  private ?User $editedBy = null;

  #[ORM\ManyToOne(inversedBy: 'stopwatchTimes')]
  private ?Client $client = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
    $this->company = $company;

    return $this;
  }

  public function __construct() {
    $this->activity = new ArrayCollection();
    $this->pdf = new ArrayCollection();
    $this->image = new ArrayCollection();
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

  public function getStart(): ?\DateTimeImmutable {
    return $this->start;
  }

  public function setStart(\DateTimeImmutable $start): self {
    $this->start = $start;

    return $this;
  }

  public function getStop(): ?\DateTimeImmutable {
    return $this->stop;
  }

  public function setStop(\DateTimeImmutable $stop): self {
    $this->stop = $stop;

    return $this;
  }

  public function getDiff(): ?int {
    return $this->diff;
  }

  public function setDiff(int $diff): self {
    $this->diff = $diff;

    return $this;
  }

  public function getMin(): ?int {
    return $this->min;
  }

  public function setMin(int $min): self {
    $this->min = $min;

    return $this;
  }

  public function isIsEdited(): ?bool {
    return $this->isEdited;
  }

  public function setIsEdited(bool $isEdited): self {
    $this->isEdited = $isEdited;

    return $this;
  }


  public function isIsDeleted(): ?bool {
    return $this->isDeleted;
  }

  public function setIsDeleted(bool $isDeleted): self {
    $this->isDeleted = $isDeleted;

    return $this;
  }

  public function isIsManuallyClosed(): ?bool {
    return $this->isManuallyClosed;
  }

  public function setIsManuallyClosed(bool $isManuallyClosed): self {
    $this->isManuallyClosed = $isManuallyClosed;

    return $this;
  }

  public function getDiffRounded(): ?int {
    return $this->diffRounded;
  }

  public function setDiffRounded(int $diffRounded): self {
    $this->diffRounded = $diffRounded;

    return $this;
  }

  public function getLon(): ?string {
    return $this->lon;
  }

  public function setLon(string $lon): self {
    $this->lon = $lon;

    return $this;
  }

  public function getLat(): ?string {
    return $this->lat;
  }

  public function setLat(string $lat): self {
    $this->lat = $lat;

    return $this;
  }


  public function getLonStop(): ?string {
    return $this->lonStop;
  }

  public function setLonStop(string $lonStop): self {
    $this->lonStop = $lonStop;

    return $this;
  }

  public function getLatStop(): ?string {
    return $this->latStop;
  }

  public function setLatStop(string $latStop): self {
    $this->latStop = $latStop;

    return $this;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  /**
   * @param DateTimeImmutable $created
   */
  public function setCreated(DateTimeImmutable $created): void {
    $this->created = $created;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getUpdated(): DateTimeImmutable {
    return $this->updated;
  }

  /**
   * @param DateTimeImmutable $updated
   */
  public function setUpdated(DateTimeImmutable $updated): void {
    $this->updated = $updated;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
  }

  public function getTaskLog(): ?TaskLog {
    return $this->taskLog;
  }

  public function setTaskLog(?TaskLog $taskLog): self {
    $this->taskLog = $taskLog;

    return $this;
  }

  /**
   * @return Collection<int, Activity>
   */
  public function getActivity(): Collection {
    return $this->activity;
  }

  public function addActivity(Activity $activity): self {
    if (!$this->activity->contains($activity)) {
      $this->activity->add($activity);
    }

    return $this;
  }

  public function removeActivity(Activity $activity): self {
    $this->activity->removeElement($activity);

    return $this;
  }

  /**
   * @return Collection<int, Pdf>
   */
  public function getPdf(): Collection {
    return $this->pdf;
  }

  public function addPdf(Pdf $pdf): self {
    if (!$this->pdf->contains($pdf)) {
      $this->pdf->add($pdf);
      $pdf->setStopwatchTime($this);
    }

    return $this;
  }

  public function removePdf(Pdf $pdf): self {
    if ($this->pdf->removeElement($pdf)) {
      // set the owning side to null (unless already changed)
      if ($pdf->getStopwatchTime() === $this) {
        $pdf->setStopwatchTime(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Image>
   */
  public function getImage(): Collection {
    return $this->image;
  }

  public function addImage(Image $image): self {
    if (!$this->image->contains($image)) {
      $this->image->add($image);
      $image->setStopwatchTime($this);
    }

    return $this;
  }

  public function removeImage(Image $image): self {
    if ($this->image->removeElement($image)) {
      // set the owning side to null (unless already changed)
      if ($image->getStopwatchTime() === $this) {
        $image->setStopwatchTime(null);
      }
    }

    return $this;
  }

  public function getDeletedBy(): ?User {
    return $this->deletedBy;
  }

  public function setDeletedBy(?User $deletedBy): self {
    $this->deletedBy = $deletedBy;

    return $this;
  }

  public function getEditedBy(): ?User {
    return $this->editedBy;
  }

  public function setEditedBy(?User $editedBy): self {
    $this->editedBy = $editedBy;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getAdditionalActivity(): ?string {
    return $this->additionalActivity;
  }

  /**
   * @param string|null $additionalActivity
   */
  public function setAdditionalActivity(?string $additionalActivity): void {
    $this->additionalActivity = $additionalActivity;
  }

  public function getClient(): ?Client {
    return $this->client;
  }

  public function setClient(?Client $client): self {
    $this->client = $client;

    return $this;
  }

  /**
   * @return int|null
   */
  public function getChecked(): ?int {
    return $this->checked;
  }

  /**
   * @param int|null $checked
   */
  public function setChecked(?int $checked): void {
    $this->checked = $checked;
  }

  /**
   * @return string|null
   */
  public function getAdditionalDesc(): ?string {
    return $this->additionalDesc;
  }

  /**
   * @param string|null $additionalDesc
   */
  public function setAdditionalDesc(?string $additionalDesc): void {
    $this->additionalDesc = $additionalDesc;
  }


}
