<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'images')]
class Image {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(name: 'original', type: Types::TEXT, nullable: true)]
  private ?string $original = null;

  #[ORM\Column(name: 'thumbnail_100x100', type: Types::TEXT, nullable: true)]
  private ?string $thumbnail100 = null;

  #[ORM\Column(name: 'thumbnail_500x500', type: Types::TEXT, nullable: true)]
  private ?string $thumbnail500 = null;

  #[ORM\Column(name: 'thumbnail_1024x768', type: Types::TEXT, nullable: true)]
  private ?string $thumbnail1024 = null;

//  #[ORM\ManyToOne(cascade: ['persist'])]
//  #[ORM\JoinColumn(nullable: true)]
//  private ?User $user = null;
//
//  #[ORM\ManyToOne(cascade: ['persist'])]
//  #[ORM\JoinColumn(nullable: true)]
//  private ?Client $client = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'image', targetEntity: User::class)]
  private Collection $users;

  #[ORM\OneToMany(mappedBy: 'image', targetEntity: Client::class)]
  private Collection $clients;

  #[ORM\ManyToOne(inversedBy: 'image')]
  private ?StopwatchTime $stopwatchTime = null;

  #[ORM\ManyToOne(inversedBy: 'image')]
  private ?CarReservation $carReservation = null;

//  #[ORM\ManyToOne(inversedBy: 'image')]
//  private ?Signature $signature = null;


  public function __construct() {
    $this->users = new ArrayCollection();
    $this->clients = new ArrayCollection();
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

//
//  public function getUser(): ?User {
//    return $this->user;
//  }
//
//  public function setUser(?User $user): self {
//    $this->user = $user;
//
//    return $this;
//  }

  /**
   * @return DateTimeImmutable
   */
  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  public function setCreated(DateTimeImmutable $created): self {
    $this->created = $created;

    return $this;
  }

  public function getUpdated(): ?DateTimeImmutable {
    return $this->updated;
  }

  public function setUpdated(DateTimeImmutable $updated): self {
    $this->updated = $updated;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getOriginal(): ?string {
    return $this->original;
  }

  /**
   * @param string|null $original
   */
  public function setOriginal(?string $original): void {
    $this->original = $original;
  }

  /**
   * @return string|null
   */
  public function getThumbnail100(): ?string {
    return $this->thumbnail100;
  }

  /**
   * @param string|null $thumbnail100
   */
  public function setThumbnail100(?string $thumbnail100): void {
    $this->thumbnail100 = $thumbnail100;
  }

  /**
   * @return string|null
   */
  public function getThumbnail500(): ?string {
    return $this->thumbnail500;
  }

  /**
   * @param string|null $thumbnail500
   */
  public function setThumbnail500(?string $thumbnail500): void {
    $this->thumbnail500 = $thumbnail500;
  }

  /**
   * @return string|null
   */
  public function getThumbnail1024(): ?string {
    return $this->thumbnail1024;
  }

  /**
   * @param string|null $thumbnail1024
   */
  public function setThumbnail1024(?string $thumbnail1024): void {
    $this->thumbnail1024 = $thumbnail1024;
  }

//  /**
//   * @return Client|null
//   */
//  public function getClient(): ?Client {
//    return $this->client;
//  }
//
//  /**
//   * @param Client|null $client
//   */
//  public function setClient(?Client $client): void {
//    $this->client = $client;
//  }

  /**
   * @return Collection<int, User>
   */
  public function getUsers(): Collection {
    return $this->users;
  }

  public function addUser(User $user): self {
    if (!$this->users->contains($user)) {
      $this->users->add($user);
      $user->setImage($this);
    }

    return $this;
  }

  public function removeUser(User $user): self {
    if ($this->users->removeElement($user)) {
      // set the owning side to null (unless already changed)
      if ($user->getImage() === $this) {
        $user->setImage(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Client>
   */
  public function getClients(): Collection
  {
      return $this->clients;
  }

  public function addClient(Client $client): self
  {
      if (!$this->clients->contains($client)) {
          $this->clients->add($client);
          $client->setImage($this);
      }

      return $this;
  }

  public function removeClient(Client $client): self
  {
      if ($this->clients->removeElement($client)) {
          // set the owning side to null (unless already changed)
          if ($client->getImage() === $this) {
              $client->setImage(null);
          }
      }

      return $this;
  }

  public function getStopwatchTime(): ?StopwatchTime
  {
      return $this->stopwatchTime;
  }

  public function setStopwatchTime(?StopwatchTime $stopwatchTime): self
  {
      $this->stopwatchTime = $stopwatchTime;

      return $this;
  }

  public function getCarReservation(): ?CarReservation
  {
      return $this->carReservation;
  }

  public function setCarReservation(?CarReservation $carReservation): self
  {
      $this->carReservation = $carReservation;

      return $this;
  }

//  public function getSignature(): ?Signature
//  {
//      return $this->signature;
//  }
//
//  public function setSignature(?Signature $signature): self
//  {
//      $this->signature = $signature;
//
//      return $this;
//  }



}
