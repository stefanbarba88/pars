<?php

namespace App\Entity;

use App\Repository\ToolReservationRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ToolReservation {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;


  #[ORM\ManyToOne(inversedBy: 'toolReservations')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\ManyToOne(inversedBy: 'toolReservations')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Tool $tool = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $descStart = null;
  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $descStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStativ = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isMiniprizma = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isBaterija = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isPunjac = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isVelikiStap = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isDistomat = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStapDodatak = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStativiKomplet = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isLetva = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isPapuce = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isGPSiStativ = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isIpad = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isExterniHDD = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isMarkiceRoto = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStativStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isMiniprizmaStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isBaterijaStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isPunjacStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isVelikiStapStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isDistomatStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStapDodatakStop = null;

  #[ORM\Column(nullable: true)]
  private ?bool $isStativiKompletStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isLetvaStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isPapuceStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isGPSiStativStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isIpadStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isExterniHDDStop = null;
  #[ORM\Column(nullable: true)]
  private ?bool $isMarkiceRotoStop = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $finished = null;

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

  public function getTool(): ?Tool {
    return $this->tool;
  }

  public function setTool(?Tool $tool): self {
    $this->tool = $tool;

    return $this;
  }

  /**
   * @return bool|null
   */
  public function getIsStativ(): ?bool {
    return $this->isStativ;
  }

  /**
   * @param bool|null $isStativ
   */
  public function setIsStativ(?bool $isStativ): void {
    $this->isStativ = $isStativ;
  }

  /**
   * @return bool|null
   */
  public function getIsMiniprizma(): ?bool {
    return $this->isMiniprizma;
  }

  /**
   * @param bool|null $isMiniprizma
   */
  public function setIsMiniprizma(?bool $isMiniprizma): void {
    $this->isMiniprizma = $isMiniprizma;
  }

  /**
   * @return bool|null
   */
  public function getIsBaterija(): ?bool {
    return $this->isBaterija;
  }

  /**
   * @param bool|null $isBaterija
   */
  public function setIsBaterija(?bool $isBaterija): void {
    $this->isBaterija = $isBaterija;
  }

  /**
   * @return bool|null
   */
  public function getIsPunjac(): ?bool {
    return $this->isPunjac;
  }

  /**
   * @param bool|null $isPunjac
   */
  public function setIsPunjac(?bool $isPunjac): void {
    $this->isPunjac = $isPunjac;
  }

  /**
   * @return bool|null
   */
  public function getIsVelikiStap(): ?bool {
    return $this->isVelikiStap;
  }

  /**
   * @param bool|null $isVelikiStap
   */
  public function setIsVelikiStap(?bool $isVelikiStap): void {
    $this->isVelikiStap = $isVelikiStap;
  }

  /**
   * @return bool|null
   */
  public function getIsDistomat(): ?bool {
    return $this->isDistomat;
  }

  /**
   * @param bool|null $isDistomat
   */
  public function setIsDistomat(?bool $isDistomat): void {
    $this->isDistomat = $isDistomat;
  }

  /**
   * @return bool|null
   */
  public function getIsStapDodatak(): ?bool {
    return $this->isStapDodatak;
  }

  /**
   * @param bool|null $isStapDodatak
   */
  public function setIsStapDodatak(?bool $isStapDodatak): void {
    $this->isStapDodatak = $isStapDodatak;
  }

  /**
   * @return bool|null
   */
  public function getIsStativiKomplet(): ?bool {
    return $this->isStativiKomplet;
  }

  /**
   * @param bool|null $isStativiKomplet
   */
  public function setIsStativiKomplet(?bool $isStativiKomplet): void {
    $this->isStativiKomplet = $isStativiKomplet;
  }

  /**
   * @return bool|null
   */
  public function getIsLetva(): ?bool {
    return $this->isLetva;
  }

  /**
   * @param bool|null $isLetva
   */
  public function setIsLetva(?bool $isLetva): void {
    $this->isLetva = $isLetva;
  }

  /**
   * @return bool|null
   */
  public function getIsPapuce(): ?bool {
    return $this->isPapuce;
  }

  /**
   * @param bool|null $isPapuce
   */
  public function setIsPapuce(?bool $isPapuce): void {
    $this->isPapuce = $isPapuce;
  }

  /**
   * @return bool|null
   */
  public function getIsGPSiStativ(): ?bool {
    return $this->isGPSiStativ;
  }

  /**
   * @param bool|null $isGPSiStativ
   */
  public function setIsGPSiStativ(?bool $isGPSiStativ): void {
    $this->isGPSiStativ = $isGPSiStativ;
  }

  /**
   * @return bool|null
   */
  public function getIsIpad(): ?bool {
    return $this->isIpad;
  }

  /**
   * @param bool|null $isIpad
   */
  public function setIsIpad(?bool $isIpad): void {
    $this->isIpad = $isIpad;
  }

  /**
   * @return bool|null
   */
  public function getIsExterniHDD(): ?bool {
    return $this->isExterniHDD;
  }

  /**
   * @param bool|null $isExterniHDD
   */
  public function setIsExterniHDD(?bool $isExterniHDD): void {
    $this->isExterniHDD = $isExterniHDD;
  }

  /**
   * @return bool|null
   */
  public function getIsMarkiceRoto(): ?bool {
    return $this->isMarkiceRoto;
  }

  /**
   * @param bool|null $isMarkiceRoto
   */
  public function setIsMarkiceRoto(?bool $isMarkiceRoto): void {
    $this->isMarkiceRoto = $isMarkiceRoto;
  }

  /**
   * @return bool|null
   */
  public function getIsStativStop(): ?bool {
    return $this->isStativStop;
  }

  /**
   * @param bool|null $isStativStop
   */
  public function setIsStativStop(?bool $isStativStop): void {
    $this->isStativStop = $isStativStop;
  }

  /**
   * @return bool|null
   */
  public function getIsMiniprizmaStop(): ?bool {
    return $this->isMiniprizmaStop;
  }

  /**
   * @param bool|null $isMiniprizmaStop
   */
  public function setIsMiniprizmaStop(?bool $isMiniprizmaStop): void {
    $this->isMiniprizmaStop = $isMiniprizmaStop;
  }

  /**
   * @return bool|null
   */
  public function getIsBaterijaStop(): ?bool {
    return $this->isBaterijaStop;
  }

  /**
   * @param bool|null $isBaterijaStop
   */
  public function setIsBaterijaStop(?bool $isBaterijaStop): void {
    $this->isBaterijaStop = $isBaterijaStop;
  }

  /**
   * @return bool|null
   */
  public function getIsPunjacStop(): ?bool {
    return $this->isPunjacStop;
  }

  /**
   * @param bool|null $isPunjacStop
   */
  public function setIsPunjacStop(?bool $isPunjacStop): void {
    $this->isPunjacStop = $isPunjacStop;
  }

  /**
   * @return bool|null
   */
  public function getIsVelikiStapStop(): ?bool {
    return $this->isVelikiStapStop;
  }

  /**
   * @param bool|null $isVelikiStapStop
   */
  public function setIsVelikiStapStop(?bool $isVelikiStapStop): void {
    $this->isVelikiStapStop = $isVelikiStapStop;
  }

  /**
   * @return bool|null
   */
  public function getIsDistomatStop(): ?bool {
    return $this->isDistomatStop;
  }

  /**
   * @param bool|null $isDistomatStop
   */
  public function setIsDistomatStop(?bool $isDistomatStop): void {
    $this->isDistomatStop = $isDistomatStop;
  }

  /**
   * @return bool|null
   */
  public function getIsStapDodatakStop(): ?bool {
    return $this->isStapDodatakStop;
  }

  /**
   * @param bool|null $isStapDodatakStop
   */
  public function setIsStapDodatakStop(?bool $isStapDodatakStop): void {
    $this->isStapDodatakStop = $isStapDodatakStop;
  }

  /**
   * @return bool|null
   */
  public function getIsStativiKompletStop(): ?bool {
    return $this->isStativiKompletStop;
  }

  /**
   * @param bool|null $isStativiKompletStop
   */
  public function setIsStativiKompletStop(?bool $isStativiKompletStop): void {
    $this->isStativiKompletStop = $isStativiKompletStop;
  }

  /**
   * @return bool|null
   */
  public function getIsLetvaStop(): ?bool {
    return $this->isLetvaStop;
  }

  /**
   * @param bool|null $isLetvaStop
   */
  public function setIsLetvaStop(?bool $isLetvaStop): void {
    $this->isLetvaStop = $isLetvaStop;
  }

  /**
   * @return bool|null
   */
  public function getIsPapuceStop(): ?bool {
    return $this->isPapuceStop;
  }

  /**
   * @param bool|null $isPapuceStop
   */
  public function setIsPapuceStop(?bool $isPapuceStop): void {
    $this->isPapuceStop = $isPapuceStop;
  }

  /**
   * @return bool|null
   */
  public function getIsGPSiStativStop(): ?bool {
    return $this->isGPSiStativStop;
  }

  /**
   * @param bool|null $isGPSiStativStop
   */
  public function setIsGPSiStativStop(?bool $isGPSiStativStop): void {
    $this->isGPSiStativStop = $isGPSiStativStop;
  }

  /**
   * @return bool|null
   */
  public function getIsIpadStop(): ?bool {
    return $this->isIpadStop;
  }

  /**
   * @param bool|null $isIpadStop
   */
  public function setIsIpadStop(?bool $isIpadStop): void {
    $this->isIpadStop = $isIpadStop;
  }

  /**
   * @return bool|null
   */
  public function getIsExterniHDDStop(): ?bool {
    return $this->isExterniHDDStop;
  }

  /**
   * @param bool|null $isExterniHDDStop
   */
  public function setIsExterniHDDStop(?bool $isExterniHDDStop): void {
    $this->isExterniHDDStop = $isExterniHDDStop;
  }

  /**
   * @return bool|null
   */
  public function getIsMarkiceRotoStop(): ?bool {
    return $this->isMarkiceRotoStop;
  }

  /**
   * @param bool|null $isMarkiceRotoStop
   */
  public function setIsMarkiceRotoStop(?bool $isMarkiceRotoStop): void {
    $this->isMarkiceRotoStop = $isMarkiceRotoStop;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getFinished(): ?DateTimeImmutable {
    return $this->finished;
  }

  /**
   * @param DateTimeImmutable|null $finished
   */
  public function setFinished(?DateTimeImmutable $finished): void {
    $this->finished = $finished;
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

  /**
   * @return string|null
   */
  public function getDescStart(): ?string {
    return $this->descStart;
  }

  /**
   * @param string|null $descStart
   */
  public function setDescStart(?string $descStart): void {
    $this->descStart = $descStart;
  }

  /**
   * @return string|null
   */
  public function getDescStop(): ?string {
    return $this->descStop;
  }

  /**
   * @param string|null $descStop
   */
  public function setDescStop(?string $descStop): void {
    $this->descStop = $descStop;
  }


}
