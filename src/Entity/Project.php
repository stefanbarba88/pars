<?php

namespace App\Entity;

use App\Classes\Data\TimerPriorityData;
use App\Classes\Slugify;
use App\Repository\ProjectRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'projects')]
class Project implements JsonSerializable {

  public function getUploadPath(): ?string {
    return $_ENV['PROJECT_IMAGE_PATH'] . $this->getCompany()->getId() . '/' . Slugify::slugify($this->title) . '/';
  }

  public function getThumbUploadPath(): ?string {
    return $_ENV['PROJECT_THUMB_PATH'] . $this->getCompany()->getId() . '/' . Slugify::slugify($this->title) . '/';
  }

  public function getNoProjectUploadPath(): ?string {
    return $_ENV['NOPROJECT_IMAGE_PATH'];
  }

  public function getNoProjectThumbUploadPath(): ?string {
    return $_ENV['NOPROJECT_THUMB_PATH'];
  }

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $important = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $editBy = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?User $createdBy = null;

  #[ORM\Column]
  private bool $isSuspended = false;

  #[ORM\Column]
  private bool $isClientView = false;

  #[ORM\Column]
  private bool $isTimeRoundUp = true;

  #[ORM\Column]
  private bool $isViewLog = false;

  #[ORM\Column]
  private bool $isEstimate = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'projects')]
  private Collection $client;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $payment = 1;

  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $type = 1;

  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $timerPriority = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $price = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerHour = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerTask = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerDay = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerMonth = null;

  #[ORM\ManyToOne(inversedBy: 'projects')]
  private ?Currency $currency = null;

  #[ORM\Column(nullable: true)]
  private ?int $roundingInterval = null;

  #[ORM\Column(nullable: true)]
  private ?int $noTasks = null;

  #[ORM\Column(nullable: true)]
  private ?int $minEntry = null;

  #[ORM\Column]
  private ?bool $isExpenses = false;

  #[ORM\Column]
  private ?bool $isPhase = false;

  #[ORM\Column]
  private ?bool $isFollowing = true;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $deadline = null;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectHistory::class, cascade: ["persist", "remove"])]
  private Collection $projectHistories;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class)]
  private Collection $tasks;


  #[ORM\ManyToOne(inversedBy: 'projects')]
  private ?Category $category = null;

  #[ORM\ManyToMany(targetEntity: Label::class, inversedBy: 'projects')]
  private Collection $label;


  #[ORM\OneToMany(mappedBy: 'project', targetEntity: Pdf::class, cascade: ["persist", "remove"])]
  private Collection $pdfs;

//  #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'projects')]
//  private Collection $team;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: true)]
  private ?Company $company = null;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: ManagerChecklist::class)]
  private Collection $managerChecklists;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: Phase::class)]
  private Collection $phase;

  public function getCompany(): ?Company {
    return $this->company;
  }

  public function setCompany(?Company $company): self {
    $this->company = $company;

    return $this;
  }

  public function __construct() {
    $this->client = new ArrayCollection();
    $this->projectHistories = new ArrayCollection();
    $this->tasks = new ArrayCollection();
    $this->label = new ArrayCollection();
    $this->pdfs = new ArrayCollection();
//    $this->team = new ArrayCollection();
    $this->managerChecklists = new ArrayCollection();
    $this->phase = new ArrayCollection();

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

  public function jsonSerialize(): array {
    return [
      'id' => $this->getId(),
      'title' => $this->getTitle(),
      'description' => $this->getDescription(),
      'important' => $this->getImportant(),
      'isSuspended' => $this->isSuspended(),
      'isTimeRoundUp' => $this->isTimeRoundUp(),
      'isEstimate' => $this->isEstimate(),
      'isClientView' => $this->isClientView(),
      'isViewLog' => $this->isViewLog(),
      'label' => $this->getLabelJson(),
      'client' => $this->getClientsJson(),
      'category' => $this->getCategoryJson(),
      'editBy' => $this->getEditByJson(),
      'payment' => $this->getPayment(),
      'type' => $this->getType(),
      'timerPriority' => $this->getTimerPriorityJson(),
      'price' => $this->getPrice(),
      'pricePerHour' => $this->getPricePerHour(),
      'pricePerTask' => $this->getPricePerTask(),
      'pricePerDay' => $this->getPricePerDay(),
      'pricePerMonth' => $this->getPricePerMonth(),
      'currency' => $this->getCurrencyJson(),
      'minEntry' => $this->getMinEntry(),
      'roundingInterval' => $this->getRoundingInterval(),
      'deadline' => $this->getDeadline(),
//      'team' => $this->getTeamJson()
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

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
  }

  /**
   * @return string|null
   */
  public function getImportant(): ?string {
    return $this->important;
  }

  /**
   * @param string|null $important
   */
  public function setImportant(?string $important): void {
    $this->important = $important;
  }


  /**
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  public function getEditByJson(): string {
    if (is_null($this->editBy)) {
      return '';
    }
    return $this->editBy->getFullName();
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

  public function getDeadline(): ?DateTimeImmutable {
    return $this->deadline;
  }

  public function setDeadline(?DateTimeImmutable $deadline): self {
    $this->deadline = $deadline;

    return $this;
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
  public function isEstimate(): bool {
    return $this->isEstimate;
  }

  /**
   * @param bool $isEstimate
   */
  public function setIsEstimate(bool $isEstimate): void {
    $this->isEstimate = $isEstimate;
  }

  /**
   * @return bool
   */
  public function isClientView(): bool {
    return $this->isClientView;
  }

  /**
   * @param bool $isClientView
   */
  public function setIsClientView(bool $isClientView): void {
    $this->isClientView = $isClientView;
  }

  /**
   * @return bool
   */
  public function isViewLog(): bool {
    return $this->isViewLog;
  }

  /**
   * @param bool $isViewLog
   */
  public function setIsViewLog(bool $isViewLog): void {
    $this->isViewLog = $isViewLog;
  }

  /**
   * @return bool
   */
  public function isTimeRoundUp(): bool {
    return $this->isTimeRoundUp;
  }

  /**
   * @param bool $isTimeRoundUp
   */
  public function setIsTimeRoundUp(bool $isTimeRoundUp): void {
    $this->isTimeRoundUp = $isTimeRoundUp;
  }

  public function getBadgeByStatus(): string {
    if ($this->isSuspended) {
      return '<span class="badge bg-yellow text-primary">Deaktiviran</span>';
    }
    return '<span class="badge bg-primary text-white">Aktivan</span>';

  }

  public function getBadgeByClientView(): string {
    if ($this->isClientView) {
      return '<span class="badge bg-secondary"><i class="ph-eye ph-sm m-1"></i></span>';
    }
    return '<span class="badge bg-yellow"><i class="ph-eye-slash ph-sm m-1"></i></span>';

  }

  /**
   * @return Collection<int, Client>
   */
  public function getClient(): Collection {
    return $this->client;
  }

  public function getClientsJson(): array {
    $clients = [];

    foreach ($this->client as $cli) {
      $clients[] = $cli->getTitle();
    }

    return $clients;
  }

  public function addClient(Client $client): self {
    if (!$this->client->contains($client)) {
      $this->client->add($client);
    }

    return $this;
  }

  public function removeClient(Client $client): self {
    $this->client->removeElement($client);

    return $this;
  }

  public function getPayment(): ?int {
    return $this->payment;
  }

  public function setPayment(int $payment): self {
    $this->payment = $payment;

    return $this;
  }

  public function getType(): ?int {
    return $this->type;
  }

  public function setType(int $type): self {
    $this->type = $type;

    return $this;
  }

  public function getTimerPriority(): ?int {
    return $this->timerPriority;
  }

  public function setTimerPriority(int $timerPriority): self {
    $this->timerPriority = $timerPriority;

    return $this;
  }

  public function getTimerPriorityJson(): ?string {
    return TimerPriorityData::getPriorityByType($this->timerPriority);
  }

  public function getPrice(): ?string {
    return $this->price;
  }

  public function setPrice(?string $price): self {
    $this->price = $price;

    return $this;
  }

  public function getPricePerHour(): ?string {
    return $this->pricePerHour;
  }

  public function setPricePerHour(?string $pricePerHour): self {
    $this->pricePerHour = $pricePerHour;

    return $this;
  }

  public function getPricePerTask(): ?string {
    return $this->pricePerTask;
  }

  public function setPricePerTask(?string $pricePerTask): self {
    $this->pricePerTask = $pricePerTask;

    return $this;
  }

  public function getPricePerDay(): ?string {
    return $this->pricePerDay;
  }

  public function setPricePerDay(?string $pricePerDay): self {
    $this->pricePerDay = $pricePerDay;

    return $this;
  }

  public function getPricePerMonth(): ?string {
    return $this->pricePerMonth;
  }

  public function setPricePerMonth(?string $pricePerMonth): self {
    $this->pricePerMonth = $pricePerMonth;

    return $this;
  }

  public function getCurrency(): ?Currency {
    return $this->currency;
  }

  public function getCurrencyJson(): string {
    if (is_null($this->currency)) {
      return '';
    }

    return $this->currency->getFormTitle();
  }


  public function setCurrency(?Currency $currency): self {
    $this->currency = $currency;

    return $this;
  }

  public function getRoundingInterval(): ?int {
    return $this->roundingInterval;
  }

  public function setRoundingInterval(?int $roundingInterval): self {
    $this->roundingInterval = $roundingInterval;

    return $this;
  }

  public function getMinEntry(): ?int {
    return $this->minEntry;
  }

  public function setMinEntry(?int $minEntry): self {
    $this->minEntry = $minEntry;

    return $this;
  }

  /**
   * @return Collection<int, ProjectHistory>
   */
  public function getProjectHistories(): Collection {
    return $this->projectHistories;
  }

  public function addProjectHistory(ProjectHistory $projectHistory): self {
    if (!$this->projectHistories->contains($projectHistory)) {
      $this->projectHistories->add($projectHistory);
      $projectHistory->setProject($this);
    }

    return $this;
  }

  public function removeProjectHistory(ProjectHistory $projectHistory): self {
    if ($this->projectHistories->removeElement($projectHistory)) {
      // set the owning side to null (unless already changed)
      if ($projectHistory->getProject() === $this) {
        $projectHistory->setProject(null);
      }
    }

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
      $task->setProject($this);
    }

    return $this;
  }

  public function removeTask(Task $task): self {
    if ($this->tasks->removeElement($task)) {
      // set the owning side to null (unless already changed)
      if ($task->getProject() === $this) {
        $task->setProject(null);
      }
    }

    return $this;
  }

  public function getCategory(): ?Category {
    return $this->category;
  }

  public function setCategory(?Category $category): self {
    $this->category = $category;

    return $this;
  }

  public function getCategoryJson(): string {
    if (is_null($this->category)) {
      return '';
    }

    return $this->category->getTitle();
  }

  /**
   * @return Collection<int, Label>
   */
  public function getLabel(): Collection {
    return $this->label;
  }

  public function addLabel(Label $label): self {
    if (!$this->label->contains($label)) {
      $this->label->add($label);
    }

    return $this;
  }

  public function removeLabel(Label $label): self {
    $this->label->removeElement($label);

    return $this;
  }

  public function getLabelJson(): array {
    $labels = [];
    foreach ($this->label as $lab) {
      $labels[] = $lab->getTitle();
    }

    return $labels;
  }

  /**
   * @return Collection<int, Pdf>
   */
  public function getPdfs(): Collection {
    return $this->pdfs;
  }

  public function addPdf(Pdf $pdf): self {
    if (!$this->pdfs->contains($pdf)) {
      $this->pdfs->add($pdf);
      $pdf->setProject($this);
    }

    return $this;
  }

  public function removePdf(Pdf $pdf): self {
    if ($this->pdfs->removeElement($pdf)) {
      // set the owning side to null (unless already changed)
      if ($pdf->getProject() === $this) {
        $pdf->setProject(null);
      }
    }

    return $this;
  }

//  /**
//   * @return Collection<int, Team>
//   */
//  public function getTeam(): Collection
//  {
//    return $this->team;
//  }

//  public function getTeamJson(): array {
//    $teams = [];
//    foreach ($this->team as $team) {
//      $members = [];
//      foreach ($team->getMember() as $member) {
//        $members[] = $member->getFullName();
//      }
//      $teams[] = [$team->getTitle(), $members];
//    }
//    return $teams;
//  }
//
//  public function addTeam(Team $team): self
//  {
//    if (!$this->team->contains($team)) {
//      $this->team->add($team);
//    }
//
//    return $this;
//  }
//
//  public function removeTeam(Team $team): self
//  {
//    $this->team->removeElement($team);
//
//    return $this;
//  }

  /**
   * @return int|null
   */
  public function getNoTasks(): ?int {
    return $this->noTasks;
  }

  /**
   * @param int|null $noTasks
   */
  public function setNoTasks(?int $noTasks): void {
    $this->noTasks = $noTasks;
  }

  /**
   * @return Collection<int, ManagerChecklist>
   */
  public function getManagerChecklists(): Collection {
    return $this->managerChecklists;
  }

  public function addManagerChecklist(ManagerChecklist $managerChecklist): self {
    if (!$this->managerChecklists->contains($managerChecklist)) {
      $this->managerChecklists->add($managerChecklist);
      $managerChecklist->setProject($this);
    }

    return $this;
  }

  public function removeManagerChecklist(ManagerChecklist $managerChecklist): self {
    if ($this->managerChecklists->removeElement($managerChecklist)) {
      // set the owning side to null (unless already changed)
      if ($managerChecklist->getProject() === $this) {
        $managerChecklist->setProject(null);
      }
    }

    return $this;
  }

  /**
   * @return bool|null
   */
  public function getIsExpenses(): ?bool {
    return $this->isExpenses;
  }

  /**
   * @param bool|null $isExpenses
   */
  public function setIsExpenses(?bool $isExpenses): void {
    $this->isExpenses = $isExpenses;
  }

  /**
   * @return Collection<int, Phase>
   */
  public function getPhase(): Collection
  {
      return $this->phase;
  }

  public function addPhase(Phase $phase): self
  {
      if (!$this->phase->contains($phase)) {
          $this->phase->add($phase);
          $phase->setProject($this);
      }

      return $this;
  }

  public function removePhase(Phase $phase): self
  {
      if ($this->phase->removeElement($phase)) {
          // set the owning side to null (unless already changed)
          if ($phase->getProject() === $this) {
              $phase->setProject(null);
          }
      }

      return $this;
  }

  public function getIsPhase(): ?bool {
    return $this->isPhase;
  }

  public function setIsPhase(?bool $isPhase): void {
    $this->isPhase = $isPhase;
  }

  public function getIsFollowing(): ?bool {
    return $this->isFollowing;
  }

  public function setIsFollowing(?bool $isFollowing): void {
    $this->isFollowing = $isFollowing;
  }



}