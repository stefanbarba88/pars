<?php

namespace App\Entity;

use App\Repository\ClientHistoryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientHistoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ClientHistory {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'clientHistories')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Client $client = null;


  #[ORM\Column]
  private ?DateTimeImmutable $created = null;

  #[ORM\Column]
  private ?DateTimeImmutable $updated = null;

  #[ORM\Column(type: Types::TEXT)]
  private ?string  $history = null;

  #[ORM\Column(length: 255)]
  private ?string  $version = null;

  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
    $this->updated = new DateTimeImmutable();
    $this->version = $this->created->format('YmdHis');
  }

  #[ORM\PreUpdate]
  public function preUpdate(): void {
    $this->updated = new DateTimeImmutable();
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

  /**
   * @return string|null
   */
  public function getVersion(): ?string {
    return $this->version;
  }

  /**
   * @param string|null $version
   */
  public function setVersion(?string $version): void {
    $this->version = $version;
  }



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

  /**
   * @return string
   */
  public function getHistory(): string {
    return $this->history;
  }

  /**
   * @param string $history
   */
  public function setHistory(string $history): void {
    $this->history = $history;
  }




}