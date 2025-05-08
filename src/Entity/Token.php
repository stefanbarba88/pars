<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column(type: 'integer')]
  private ?int $id = null;

  #[ORM\Column(type: 'integer')]
  private int $userId; // ID korisnika iz tvoje baze

  #[ORM\Column( length: 255)]
  private string $accessToken;

  #[ORM\Column( length: 255, nullable: true)]
  private ?string $refreshToken;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private DateTimeImmutable $expiresAt;

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

  /**
   * @return int|null
   */
  public function getId(): ?int {
    return $this->id;
  }

  /**
   * @param int|null $id
   */
  public function setId(?int $id): void {
    $this->id = $id;
  }

  /**
   * @return int
   */
  public function getUserId(): int {
    return $this->userId;
  }

  /**
   * @param int $userId
   */
  public function setUserId(int $userId): void {
    $this->userId = $userId;
  }

  /**
   * @return string
   */
  public function getAccessToken(): string {
    return $this->accessToken;
  }

  /**
   * @param string $accessToken
   */
  public function setAccessToken(string $accessToken): void {
    $this->accessToken = $accessToken;
  }

  /**
   * @return string|null
   */
  public function getRefreshToken(): ?string {
    return $this->refreshToken;
  }

  /**
   * @param string|null $refreshToken
   */
  public function setRefreshToken(?string $refreshToken): void {
    $this->refreshToken = $refreshToken;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getExpiresAt(): DateTimeImmutable {
    return $this->expiresAt;
  }

  /**
   * @param DateTimeImmutable $expiresAt
   */
  public function setExpiresAt(DateTimeImmutable $expiresAt): void {
    $this->expiresAt = $expiresAt;
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


}
