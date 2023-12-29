<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'clients')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['pib', 'company'], message: 'U bazi već postoji klijent sa ovim pib-om.')]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client implements JsonSerializable {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  public function getImageUploadPath(): ?string {
    return $_ENV['USER_IMAGE_PATH'] . $this->getCompany()->getId() . '/'. date('Y/m/d/');
  }

  public function getAvatarUploadPath(): ?string {
    return $_ENV['USER_AVATAR_PATH'] . $this->getCompany()->getId() . '/'. date('Y/m/d/');
  }

  public function getThumbUploadPath(): ?string {
    return $_ENV['USER_THUMB_PATH'] . $this->getCompany()->getId() . '/'. date('Y/m/d/');
  }

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $adresa = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?City $grad = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon1 = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon2 = null;
  #[ORM\Column]
  private ?string $pib = null;


  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isSerbian = true;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'client', targetEntity: ClientHistory::class, cascade: ["persist", "remove"])]
  private Collection $clientHistories;

  #[ORM\ManyToOne(inversedBy: 'clients')]
  private ?Image $image = null;

  #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'clients')]
  private Collection $contact;

  #[ORM\OneToMany(mappedBy: 'client', targetEntity: StopwatchTime::class)]
  private Collection $stopwatchTimes;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;
  public function getCompany(): ?Company
  {
    return $this->company;
  }

  public function setCompany(?Company $company): self
  {
    $this->company = $company;

    return $this;
  }

  public function __construct() {
    $this->clientHistories = new ArrayCollection();
    $this->contact = new ArrayCollection();
    $this->stopwatchTimes = new ArrayCollection();
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

  public function jsonSerialize(): array {

    return [
      'id' => $this->getId(),
      'title' => $this->getTitle(),
      'adresa' => $this->getAdresa(),
      'grad' => $this->getGrad(),
      'telefon1' => $this->getTelefon1(),
      'telefon2' => $this->getTelefon2(),
      'pib' => $this->getPib(),
      'contact' => $this->getContact(),
      'editBy' => $this->editBy,
      'isSuspended' => $this->isSuspended(),
      'isSerbian' => $this->isSerbian(),
    ];
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getTitle(): ?string {
    return $this->title;
  }

  public function setTitle(string $title): self {
    $this->title = $title;

    return $this;
  }

  public function getAdresa(): ?string {
    return $this->adresa;
  }

  public function setAdresa(?string $adresa): self {
    $this->adresa = $adresa;

    return $this;
  }

  public function getPib(): ?string {
    return $this->pib;
  }

  public function setPib(?string $pib): self {
    $this->pib = $pib;

    return $this;
  }


  /**
   * @return City|null
   */
  public function getGrad(): ?City {
    return $this->grad;
  }

  /**
   * @param City|null $grad
   */
  public function setGrad(?City $grad): void {
    $this->grad = $grad;
  }

  /**
   * @return string|null
   */
  public function getTelefon1(): ?string {
    return $this->telefon1;
  }

  /**
   * @param string|null $telefon1
   */
  public function setTelefon1(?string $telefon1): void {
    $this->telefon1 = $telefon1;
  }

  /**
   * @return string|null
   */
  public function getTelefon2(): ?string {
    return $this->telefon2;
  }

  /**
   * @param string|null $telefon2
   */
  public function setTelefon2(?string $telefon2): void {
    $this->telefon2 = $telefon2;
  }

  /**
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  /**
   * @param User|null $editBy
   */
  public function setEditBy(?User $editBy): void {
    $this->editBy = $editBy;
  }

  /**
   * @return User|null
   */
  public function getCreatedBy(): ?User {
    return $this->createdBy;
  }

  /**
   * @param User|null $createdBy
   */
  public function setCreatedBy(?User $createdBy): void {
    $this->createdBy = $createdBy;
  }

  /**
   * @return bool
   */
  public function isSuspended(): bool {
    return $this->isSuspended;
  }

  /**
   * @param bool $isSuspended
   */
  public function setIsSuspended(bool $isSuspended): void {
    $this->isSuspended = $isSuspended;
  }

  /**
   * @return bool
   */
  public function isSerbian(): bool {
    return $this->isSerbian;
  }

  /**
   * @param bool $isSerbian
   */
  public function setIsSerbian(bool $isSerbian): void {
    $this->isSerbian = $isSerbian;
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
  
  public function getBadgeByStatus(): string {
    if ($this->isSuspended) {
      return '<span class="badge bg-yellow text-primary">Deaktiviran</span>';
    }
    return '<span class="badge bg-primary text-white">Aktivan</span>';

  }

  /**
   * @return Collection<int, ClientHistory>
   */
  public function getClientHistories(): Collection {
    return $this->clientHistories;
  }

  public function addClientHistory(ClientHistory $clientHistory): self {
    if (!$this->clientHistories->contains($clientHistory)) {
      $this->clientHistories->add($clientHistory);
      $clientHistory->setClient($this);
    }

    return $this;
  }

  public function removeClientHistory(ClientHistory $clientHistory): self {
    if ($this->clientHistories->removeElement($clientHistory)) {
      // set the owning side to null (unless already changed)
      if ($clientHistory->getClient() === $this) {
        $clientHistory->setClient(null);
      }
    }

    return $this;
  }

  public function getImage(): ?Image {
    return $this->image;
  }

  public function setImage(?Image $image): self {
    $this->image = $image;

    return $this;
  }

  /**
   * @return Collection<int, User>
   */
  public function getContact(): Collection
  {
      return $this->contact;
  }

  public function addContact(User $contact): self
  {
      if (!$this->contact->contains($contact)) {
          $this->contact->add($contact);
      }

      return $this;
  }

  public function removeContact(User $contact): self
  {
      $this->contact->removeElement($contact);

      return $this;
  }

  /**
   * @return Collection<int, StopwatchTime>
   */
  public function getStopwatchTimes(): Collection
  {
      return $this->stopwatchTimes;
  }

  public function addStopwatchTime(StopwatchTime $stopwatchTime): self
  {
      if (!$this->stopwatchTimes->contains($stopwatchTime)) {
          $this->stopwatchTimes->add($stopwatchTime);
          $stopwatchTime->setClient($this);
      }

      return $this;
  }

  public function removeStopwatchTime(StopwatchTime $stopwatchTime): self
  {
      if ($this->stopwatchTimes->removeElement($stopwatchTime)) {
          // set the owning side to null (unless already changed)
          if ($stopwatchTime->getClient() === $this) {
              $stopwatchTime->setClient(null);
          }
      }

      return $this;
  }

}
