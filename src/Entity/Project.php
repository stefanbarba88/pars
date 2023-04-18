<?php

namespace App\Entity;

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
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $title = null;

  #[ORM\Column(type: Types::TEXT, nullable: true,)]
  private ?string $description = null;

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
  private bool $isTimeRoundUp = false;

  #[ORM\Column]
  private bool $isEstimate = false;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'projects')]
  private Collection $category;

  #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'projects')]
  private Collection $client;

  #[ORM\ManyToOne(inversedBy: 'projects')]
  private ?Label $label = null;

  #[ORM\Column(type: Types::SMALLINT)]
  private ?int $payment = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $price = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerHour = null;

  #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
  private ?string $pricePerTask = null;

  #[ORM\ManyToOne(inversedBy: 'projects')]
  private ?Currency $currency = null;

  #[ORM\Column(nullable: true)]
  private ?int $roundingInterval = null;

  #[ORM\Column(nullable: true)]
  private ?int $minEntry = null;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
  private ?DateTimeImmutable $deadline = null;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectHistory::class, cascade: ["persist", "remove"])]
  private Collection $projectHistories;

  #[ORM\OneToMany(mappedBy: 'project', targetEntity: Task::class)]
  private Collection $tasks;

  public function __construct() {
    $this->category = new ArrayCollection();
    $this->client = new ArrayCollection();
    $this->projectHistories = new ArrayCollection();
    $this->tasks = new ArrayCollection();
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
      'isSuspended' => $this->isSuspended(),
      'isTimeRoundUp' => $this->isTimeRoundUp(),
      'isEstimate' => $this->isEstimate(),
      'isClientView' => $this->isClientView(),
      'category' => $this->getCategoriesJson(),
      'client' => $this->getClientsJson(),
      'label' => $this->getLabelJson(),
      'editBy' => $this->getEditByJson(),
      'payment' => $this->getPayment(),
      'price' => $this->getPrice(),
      'pricePerHour' => $this->getPricePerHour(),
      'pricePerTask' => $this->getPricePerTask(),
      'currency' => $this->getCurrencyJson(),
      'minEntry' => $this->getMinEntry(),
      'roundingInterval' => $this->getRoundingInterval(),
      'deadline' => $this->getDeadline()
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
   * @return User|null
   */
  public function getEditBy(): ?User {
    return $this->editBy;
  }

  public function getEditByJson(): string {

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
      return '<span class="badge bg-danger">Neaktivan</span>';
    }
    return '<span class="badge bg-info">Aktivan</span>';

  }

  public function getBadgeByClientView(): string {
    if ($this->isClientView) {
      return '<span class="badge bg-info"><i class="ph-eye ph-sm m-1"></i></span>';
    }
    return '<span class="badge bg-danger"><i class="ph-eye-slash ph-sm m-1"></i></span>';

  }

  /**
   * @return Collection<int, Category>
   */
  public function getCategory(): Collection {
    return $this->category;
  }

  public function getCategoriesJson(): array {
    $categories = [];

    foreach ($this->category as $cat) {
      $categories[] = $cat->getTitle();
    }

    return $categories;
  }

  public function addCategory(Category $category): self {
    if (!$this->category->contains($category)) {
      $this->category->add($category);
    }

    return $this;
  }

  public function removeCategory(Category $category): self {
    $this->category->removeElement($category);

    return $this;
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

  public function getLabel(): ?Label {
    return $this->label;
  }

  public function getLabelJson(): string {

    return $this->label->getTitle();
  }

  public function setLabel(?Label $label): self {
    $this->label = $label;

    return $this;
  }

  public function getPayment(): ?int {
    return $this->payment;
  }

  public function setPayment(int $payment): self {
    $this->payment = $payment;

    return $this;
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

  public function getCurrency(): ?Currency {
    return $this->currency;
  }

  public function getCurrencyJson(): string {

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
  public function getTasks(): Collection
  {
      return $this->tasks;
  }

  public function addTask(Task $task): self
  {
      if (!$this->tasks->contains($task)) {
          $this->tasks->add($task);
          $task->setProject($this);
      }

      return $this;
  }

  public function removeTask(Task $task): self
  {
      if ($this->tasks->removeElement($task)) {
          // set the owning side to null (unless already changed)
          if ($task->getProject() === $this) {
              $task->setProject(null);
          }
      }

      return $this;
  }

}
