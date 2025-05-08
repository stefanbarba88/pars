<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\Column(nullable: true)]
  private ?int $value1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $value2 = null;

  #[ORM\ManyToOne(inversedBy: 'vote')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Survey $survey = null;

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

  public function getId(): ?int {
    return $this->id;
  }

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
    $this->user = $user;

    return $this;
  }

  public function getSurvey(): ?Survey {
    return $this->survey;
  }

  public function setSurvey(?Survey $survey): self {
    $this->survey = $survey;

    return $this;
  }

  /**
   * @return int|null
   */
  public function getValue1(): ?int {
    return $this->value1;
  }

  /**
   * @param int|null $value1
   */
  public function setValue1(?int $value1): void {
    $this->value1 = $value1;
  }

  /**
   * @return int|null
   */
  public function getValue2(): ?int {
    return $this->value2;
  }

  /**
   * @param int|null $value2
   */
  public function setValue2(?int $value2): void {
    $this->value2 = $value2;
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
