<?php

namespace App\Entity;

use App\Classes\Data\PolData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  public function getUploadPath(): ?string {
    return $_ENV['USER_PATH'] . date('Y/m/d/');
  }

  #[ORM\Column(length: 180, unique: true)]
  private ?string $email = null;

  #[ORM\Column(length: 255)]
  private ?string $ime = null;

  #[ORM\Column(length: 255)]
  private ?string $prezime = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $jmbg = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $slika = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $adresa = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $grad = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon1 = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $telefon2 = null;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $pol = null;

  #[ORM\Column(name: 'vrsta_zaposlenja', length: 2, nullable: true)]
  private ?int $vrstaZaposlenja = null;

  #[ORM\Column(name: 'datum_rodjenja', type: Types::DATE_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumRodjenja = null;

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
  public function getSlika(): ?string {
    return $this->slika;
  }

  /**
   * @param string|null $slika
   */
  public function setSlika(?string $slika): void {
    $this->slika = $slika;
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
   * @return string|null
   */
  public function getGrad(): ?string {
    return $this->grad;
  }

  /**
   * @param string|null $grad
   */
  public function setGrad(?string $grad): void {
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

  /**
   * @see UserInterface
   */
  public function getRoles(): array {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function setRoles(array $roles): self {
    $this->roles = $roles;

    return $this;
  }

  public function setRole(string $role): void {
    $this->role = $role;
    $this->setRoles([$role]);

    $this->setUserType(UserRolesData::getTypeByRole($role));
  }

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


}
