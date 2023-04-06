<?php

namespace App\Entity;

use App\Classes\Data\PolData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'U bazi već postoji korisnik sa ovim email nalogom')]
class User implements UserInterface, JsonSerializable {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  public function getImageUploadPath(): ?string {
    return $_ENV['USER_IMAGE_PATH'] . date('Y/m/d/');
  }
  public function getAvatarUploadPath(): ?string {
    return $_ENV['USER_AVATAR_PATH'] . date('Y/m/d/');
  }
  public function getThumbUploadPath(): ?string {
    return $_ENV['USER_THUMB_PATH'] . date('Y/m/d/');
  }

  #[ORM\Column(length: 180, unique: true)]
  private ?string $email = null;

  #[ORM\Column(length: 255)]
  private ?string $ime = null;

  #[ORM\Column(length: 255)]
  private ?string $prezime = null;

  #[ORM\Column(length: 13, nullable: true)]
  private ?string $jmbg = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $adresa = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?City $grad = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon1 = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon2 = null;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $pol = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?ZaposleniPozicija $pozicija = null;

  #[ORM\Column(name: 'vrsta_zaposlenja', length: 2, nullable: true)]
  private ?int $vrstaZaposlenja = null;

  #[ORM\Column(name: 'datum_rodjenja', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumRodjenja = null;

  #[ORM\ManyToOne(targetEntity: self::class)]
  private ?self $editBy = null;

  #[ORM\ManyToOne(targetEntity: self::class)]
  private ?self $createdBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $userType = null;

  #[ORM\Column]
  private array $roles = [];

  /*
  * koristimo samo u formi
  */
  private string $role = '';

  /*
  * koristimo samo u formi
  */
  private ?string $plainPassword = null;

  /**
   * @var string The hashed password
   */
  #[ORM\Column]
  private ?string $password = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserHistory::class, cascade : ["persist", "remove"])]
  private Collection $userHistories;

  public function __construct() {
    $this->userHistories = new ArrayCollection();
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

  public function __clone() {
    if ($this->id) {
      $this->id = null;
    }
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getEmail(): ?string {
    return $this->email;
  }

  public function setEmail(string $email): self {
    $this->email = $email;

    return $this;
  }

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
  public function getIme(): ?string {
    return $this->ime;
  }

  /**
   * @param string|null $ime
   */
  public function setIme(?string $ime): void {
    $this->ime = $ime;
  }

  /**
   * @return string|null
   */
  public function getPrezime(): ?string {
    return $this->prezime;
  }

  /**
   * @param string|null $prezime
   */
  public function setPrezime(?string $prezime): void {
    $this->prezime = $prezime;
  }

  public function getFullName(): string {
    return $this->ime . ' ' . $this->prezime;
  }

  /**
   * @return string|null
   */
  public function getJmbg(): ?string {
    return $this->jmbg;
  }

  /**
   * @param string|null $jmbg
   */
  public function setJmbg(?string $jmbg): void {
    $this->jmbg = $jmbg;
  }

  /**
   * @return string|null
   */
  public function getAdresa(): ?string {
    return $this->adresa;
  }

  /**
   * @param string|null $adresa
   */
  public function setAdresa(?string $adresa): void {
    $this->adresa = $adresa;
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
   * @return ZaposleniPozicija|null
   */
  public function getPozicija(): ?ZaposleniPozicija {
    return $this->pozicija;
  }

  /**
   * @param ZaposleniPozicija|null $pozicija
   */
  public function setPozicija(?ZaposleniPozicija $pozicija): void {
    $this->pozicija = $pozicija;
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
   * @return int|null
   */
  public function getPol(): ?int {
    return $this->pol;
  }

  /**
   * @param int|null $pol
   */
  public function setPol(?int $pol): void {
    $this->pol = $pol;
  }

  public function getPolData(): string {
    return PolData::POL[$this->getPol()];
  }

  /**
   * @return int|null
   */
  public function getVrstaZaposlenja(): ?int {
    return $this->vrstaZaposlenja;
  }

  /**
   * @param int|null $vrstaZaposlenja
   */
  public function setVrstaZaposlenja(?int $vrstaZaposlenja): void {
    $this->vrstaZaposlenja = $vrstaZaposlenja;
  }

  public function getVrstaZaposlenjaData(): string {
    return VrstaZaposlenjaData::VRSTA_ZAPOSLENJA[$this->getVrstaZaposlenja()];
  }


  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string {
    return (string)$this->email;
  }

  #[ORM\PostLoad]
  public function postLoad(): void {
    $this->role = UserRolesData::userRole($this);
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array {
    $roles = empty($this->roles) ? [] : $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function setRoles(array $roles): self {
    $this->roles = $roles;

    return $this;
  }

//  public function setRole(string $role): void {
//    $this->role = $role;
//    $this->setRoles([$role]);
//
//    $this->setUserType(UserRolesData::getTypeByRole($role));
//  }

  /**
   * @return int|null
   */
  public function getUserType(): ?int {
    return $this->userType;
  }

  /**
   * @param int|null $userType
   */
  public function setUserType(?int $userType): void {
    $this->userType = $userType;
    $this->setRoles([UserRolesData::getRoleByType($userType)]);
  }

  public function getBadgeByUserType(): string {
    return UserRolesData::getBadgeByType($this->userType);
  }

  public function getBadgeByStatus(): string {
    if ($this->isSuspended) {
      return '<span class="badge bg-danger">Deaktiviran</span>';
    }
    return '<span class="badge bg-info">Aktiviran</span>';

  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): string {
    return $this->password;
  }

  public function setPassword(string $password): self {
    $this->password = $password;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getPlainPassword(): ?string {
    return $this->plainPassword;
  }

  /**
   * @param string|null $plainPassword
   */
  public function setPlainPassword(?string $plainPassword): void {
    $this->plainPassword = $plainPassword;
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials() {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
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
   * @return DateTimeImmutable|null
   */
  public function getDatumRodjenja(): ?DateTimeImmutable {
    return $this->datumRodjenja;
  }

  /**
   * @param DateTimeImmutable|null $datumRodjenja
   */
  public function setDatumRodjenja(?DateTimeImmutable $datumRodjenja): void {
    $this->datumRodjenja = $datumRodjenja;
  }

  public function getEditBy(): ?self {
    return $this->editBy;
  }

  public function setEditBy(?self $editBy): self {
    $this->editBy = $editBy;

    return $this;
  }

  public function getCreatedBy(): ?self {
    return $this->createdBy;
  }

  public function setCreatedBy(?self $createdBy): self {
    $this->createdBy = $createdBy;

    return $this;
  }

  public function jsonSerialize(): array {

    return [
      'id' => $this->getId(),
      'ime' => $this->getIme(),
      'prezime' => $this->getPrezime(),
      'adresa' => $this->getAdresa(),
      'grad' => $this->getGrad(),
      'telefon1' => $this->getTelefon1(),
      'telefon2' => $this->getTelefon2(),
      'jmbg' => $this->getJmbg(),
      'pol' => $this->pol,
      'pozicija' => $this->getPozicija(),
      'vrstaZaposlenja' => $this->getVrstaZaposlenja(),
      'datumRodjenja' => $this->getDatumRodjenja(),
      'userType' => $this->getUserType(),
      'roles' => $this->getRoles(),
      'editBy' => $this->editBy,
      'isSuspended' => $this->isSuspended(),
      'email' => $this->getEmail(),
    ];
  }

  /**
   * @return Collection<int, UserHistory>
   */
  public function getUserHistories(): Collection {
    return $this->userHistories;
  }

  public function addUserHistory(UserHistory $userHistory): self {
    if (!$this->userHistories->contains($userHistory)) {
      $this->userHistories->add($userHistory);
      $userHistory->setUser($this);
    }

    return $this;
  }

  public function removeUserHistory(UserHistory $userHistory): self {
    if ($this->userHistories->removeElement($userHistory)) {
      // set the owning side to null (unless already changed)
      if ($userHistory->getUser() === $this) {
        $userHistory->setUser(null);
      }
    }

    return $this;
  }

}
