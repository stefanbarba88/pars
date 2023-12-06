<?php

namespace App\Entity;

use App\Classes\Data\PolData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaZaposlenjaData;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'U bazi već postoji korisnik sa ovim email nalogom')]
class User implements UserInterface, JsonSerializable, PasswordAuthenticatedUserInterface {
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

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $vozacki = null;

  #[ORM\Column]
  private bool $isLekarski = false;

  #[ORM\Column]
  private bool $isPrvaPomoc = false;

  #[ORM\Column]
  private bool $isInTask = false;

  #[ORM\Column(name: 'slava', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $slava = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?ZaposleniPozicija $pozicija = null;

  #[ORM\Column(name: 'vrsta_zaposlenja', length: 2, nullable: true)]
  private ?int $vrstaZaposlenja = null;

  #[ORM\Column(name: 'car', length: 3, nullable: true)]
  private ?int $car = null;

  #[ORM\Column(name: 'datum_rodjenja', type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $datumRodjenja = null;

  #[ORM\ManyToOne(targetEntity: self::class)]
  private ?self $editBy = null;

  #[ORM\ManyToOne(targetEntity: self::class)]
  private ?self $createdBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isMobile = false;

  #[ORM\Column]
  private bool $isLaptop = false;

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

  /*
  * koristimo samo radi ogranicavanja koja rola moze da kreira
  */
  private ?int $plainUserType = null;

  /**
   * @var string The hashed password
   */
  #[ORM\Column]
  private ?string $password = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserHistory::class, cascade: ["persist", "remove"])]
  private Collection $userHistories;

  #[ORM\ManyToOne(inversedBy: 'users')]
  private ?Image $image = null;

  #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'assignedUsers')]
  private Collection $tasks;

  #[ORM\OneToMany(mappedBy: 'User', targetEntity: Comment::class)]
  private Collection $comments;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notes::class)]
  private Collection $notes;

  #[ORM\ManyToMany(targetEntity: Client::class, mappedBy: 'contact')]
  private Collection $clients;

  #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'member')]
  private Collection $teams;

  #[ORM\OneToMany(mappedBy: 'driver', targetEntity: CarReservation::class)]
  private Collection $carReservations;


  #[ORM\ManyToMany(targetEntity: Calendar::class, mappedBy: 'user')]
  private Collection $calendars;

  #[ORM\OneToMany(mappedBy: 'user', targetEntity: ToolReservation::class)]
  private Collection $toolReservations;

  #[ORM\Column(nullable: true)]
  private ?int $ProjectType = null;


  public function __construct() {
    $this->userHistories = new ArrayCollection();
    $this->tasks = new ArrayCollection();
    $this->comments = new ArrayCollection();
    $this->notes = new ArrayCollection();
    $this->clients = new ArrayCollection();
    $this->teams = new ArrayCollection();
    $this->carReservations = new ArrayCollection();
    $this->calendars = new ArrayCollection();
    $this->toolReservations = new ArrayCollection();

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

  public function getInitials(): string {

    return (mb_substr($this->ime, 0, 1) . '. ' . mb_substr($this->prezime, 0, 1) . '.');
  }

  public function getNameWithFirstLetter(): string {

    return $this->ime . ' ' . (mb_substr($this->prezime, 0, 1) . '.');
  }

  public function getNameForForm(): string {
    return $this->ime . ' ' . $this->prezime . ' - ' . $this->pozicija->getTitle();
  }

  public function getRoleTitleByType(): string {
    return UserRolesData::getTitleByType($this->userType);
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
   * @return int|null
   */
  public function getCar(): ?int {
    return $this->car;
  }

  /**
   * @param int|null $car
   */
  public function setCar(?int $car): void {
    $this->car = $car;
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

  public function projectByType(): string {
    if (!is_null($this->getProjectType())) {
      $projekat = TipProjektaData::TIP[$this->getProjectType()];
    }
    else {
      $projekat = '';
    }
    return $projekat;
  }

  public function getBadgeByStatus(): string {
    if ($this->isSuspended) {
      return '<span class="badge bg-yellow text-primary">Deaktiviran</span>';
    }
    return '<span class="badge bg-primary text-white">Aktivan</span>';

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
   * @return int|null
   */
  public function getPlainUserType(): ?int {
    return $this->plainUserType;
  }

  /**
   * @param int|null $plainUserType
   */
  public function setPlainUserType(?int $plainUserType): void {
    $this->plainUserType = $plainUserType;
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
   * @return bool
   */
  public function isMobile(): bool {
    return $this->isMobile;
  }

  /**
   * @param bool $isMobile
   */
  public function setIsMobile(bool $isMobile): void {
    $this->isMobile = $isMobile;
  }

  /**
   * @return bool
   */
  public function isLaptop(): bool {
    return $this->isLaptop;
  }

  /**
   * @param bool $isLaptop
   */
  public function setIsLaptop(bool $isLaptop): void {
    $this->isLaptop = $isLaptop;
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
      'isLaptop' => $this->isLaptop(),
      'isMobile' => $this->isMobile(),
      'isLekarski' => $this->isLekarski(),
      'isPrvaPomoc' => $this->isPrvaPomoc(),
      'vozacki' => $this->getVozacki(),
      'slava' => $this->getSlava(),
      'isSuspended' => $this->isSuspended(),
      'email' => $this->getEmail(),
      'image' => $this->getImage(),
      'projectType' => $this->getProjectType()
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

  public function getImage(): ?Image {
    return $this->image;
  }

  public function setImage(?Image $image): self {
    $this->image = $image;

    return $this;
  }

  /**
   * @return Collection<int, Task>
   */
  public function getTasks(): Collection {
    return $this->tasks;
  }

  public function addTask(Task $task): self {
    if (!$this->tasks->contains($task)) {
      $this->tasks->add($task);
      $task->addAssignedUser($this);
    }

    return $this;
  }

  public function removeTask(Task $task): self {
    if ($this->tasks->removeElement($task)) {
      $task->removeAssignedUser($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, Comment>
   */
  public function getComments(): Collection {
    return $this->comments;
  }

  public function addComment(Comment $comment): self {
    if (!$this->comments->contains($comment)) {
      $this->comments->add($comment);
      $comment->setUser($this);
    }

    return $this;
  }

  public function removeComment(Comment $comment): self {
    if ($this->comments->removeElement($comment)) {
      // set the owning side to null (unless already changed)
      if ($comment->getUser() === $this) {
        $comment->setUser(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Notes>
   */
  public function getNotes(): Collection {
    return $this->notes;
  }

  public function addNote(Notes $note): self {
    if (!$this->notes->contains($note)) {
      $this->notes->add($note);
      $note->setUser($this);
    }

    return $this;
  }

  public function removeNote(Notes $note): self {
    if ($this->notes->removeElement($note)) {
      // set the owning side to null (unless already changed)
      if ($note->getUser() === $this) {
        $note->setUser(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, Client>
   */
  public function getClients(): Collection {
    return $this->clients;
  }

  public function addClient(Client $client): self {
    if (!$this->clients->contains($client)) {
      $this->clients->add($client);
      $client->addContact($this);
    }

    return $this;
  }

  public function removeClient(Client $client): self {
    if ($this->clients->removeElement($client)) {
      $client->removeContact($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, Team>
   */
  public function getTeams(): Collection {
    return $this->teams;
  }

  public function addTeam(Team $team): self {
    if (!$this->teams->contains($team)) {
      $this->teams->add($team);
      $team->addMember($this);
    }

    return $this;
  }

  public function removeTeam(Team $team): self {
    if ($this->teams->removeElement($team)) {
      $team->removeMember($this);
    }

    return $this;
  }

  /**
   * @return Collection<int, CarReservation>
   */
  public function getCarReservations(): Collection {
    return $this->carReservations;
  }

  public function addCarReservation(CarReservation $carReservation): self {
    if (!$this->carReservations->contains($carReservation)) {
      $this->carReservations->add($carReservation);
      $carReservation->setDriver($this);
    }

    return $this;
  }

  public function removeCarReservation(CarReservation $carReservation): self {
    if ($this->carReservations->removeElement($carReservation)) {
      // set the owning side to null (unless already changed)
      if ($carReservation->getDriver() === $this) {
        $carReservation->setDriver(null);
      }
    }

    return $this;
  }


  /**
   * @return Collection<int, Calendar>
   */
  public function getCalendars(): Collection
  {
      return $this->calendars;
  }

  public function addCalendar(Calendar $calendar): self
  {
      if (!$this->calendars->contains($calendar)) {
          $this->calendars->add($calendar);
          $calendar->addUser($this);
      }

      return $this;
  }

  public function removeCalendar(Calendar $calendar): self
  {
      if ($this->calendars->removeElement($calendar)) {
          $calendar->removeUser($this);
      }

      return $this;
  }

  /**
   * @return int|null
   */
  public function getVozacki(): ?int {
    return $this->vozacki;
  }

  /**
   * @param int|null $vozacki
   */
  public function setVozacki(?int $vozacki): void {
    $this->vozacki = $vozacki;
  }

  /**
   * @return bool
   */
  public function isLekarski(): bool {
    return $this->isLekarski;
  }

  /**
   * @param bool $isLekarski
   */
  public function setIsLekarski(bool $isLekarski): void {
    $this->isLekarski = $isLekarski;
  }

  /**
   * @return bool
   */
  public function isPrvaPomoc(): bool {
    return $this->isPrvaPomoc;
  }

  /**
   * @param bool $isPrvaPomoc
   */
  public function setIsPrvaPomoc(bool $isPrvaPomoc): void {
    $this->isPrvaPomoc = $isPrvaPomoc;
  }

  /**
   * @return DateTimeImmutable|null
   */
  public function getSlava(): ?DateTimeImmutable {
    return $this->slava;
  }

  /**
   * @param DateTimeImmutable|null $slava
   */
  public function setSlava(?DateTimeImmutable $slava): void {
    $this->slava = $slava;
  }



  /**
   * @return bool
   */
  public function isInTask(): bool {
    return $this->isInTask;
  }

  /**
   * @param bool $isInTask
   */
  public function setIsInTask(bool $isInTask): void {
    $this->isInTask = $isInTask;
  }

  /**
   * @return Collection<int, ToolReservation>
   */
  public function getToolReservations(): Collection
  {
      return $this->toolReservations;
  }

  public function addToolReservation(ToolReservation $toolReservation): self
  {
      if (!$this->toolReservations->contains($toolReservation)) {
          $this->toolReservations->add($toolReservation);
          $toolReservation->setUser($this);
      }

      return $this;
  }

  public function removeToolReservation(ToolReservation $toolReservation): self
  {
      if ($this->toolReservations->removeElement($toolReservation)) {
          // set the owning side to null (unless already changed)
          if ($toolReservation->getUser() === $this) {
              $toolReservation->setUser(null);
          }
      }

      return $this;
  }

  public function getProjectType(): ?int
  {
      return $this->ProjectType;
  }

  public function setProjectType(?int $ProjectType): self
  {
      $this->ProjectType = $ProjectType;

      return $this;
  }


}
