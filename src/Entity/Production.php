<?php

namespace App\Entity;

use App\Classes\Data\TaskStatusData;
use App\Repository\ProductionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Production
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $productKey = null;


    #[ORM\Column]
    private bool $isSuspended = false;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $archive = null;

    #[ORM\Column(type: Types::TEXT, nullable: true,)]
    private ?string $progres = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $percent = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $status = TaskStatusData::NIJE_ZAPOCETO;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deadline;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column]
    private DateTimeImmutable $updated;


    #[ORM\ManyToOne(inversedBy: 'productions')]
    private ?Project $project = null;

    #[ORM\ManyToOne(inversedBy: 'productions')]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'production', targetEntity: Deo::class, cascade: ['persist'])]
    private Collection $deo;

    #[ORM\OneToMany(mappedBy: 'production', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'production', targetEntity: ManagerChecklist::class)]
    private Collection $managerChecklists;

    public function __construct()
    {
        $this->deo = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->managerChecklists = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getProductKey(): ?string {
        return $this->productKey;
    }

    public function setProductKey(?string $productKey): void {
        $this->productKey = $productKey;
    }


    public function isSuspended(): bool {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): void {
        $this->isSuspended = $isSuspended;
    }

    public function getPercent(): ?string {
        return $this->percent;
    }

    public function setPercent(?string $percent): void {
        $this->percent = $percent;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(?int $status): void {
        $this->status = $status;
    }

    public function getDeadline(): ?DateTimeImmutable {
        return $this->deadline;
    }

    public function setDeadline(?DateTimeImmutable $deadline): void {
        $this->deadline = $deadline;
    }

    public function getCreated(): DateTimeImmutable {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): void {
        $this->created = $created;
    }

    public function getUpdated(): DateTimeImmutable {
        return $this->updated;
    }

    public function setUpdated(DateTimeImmutable $updated): void {
        $this->updated = $updated;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
    /**
     * @return array|null
     */
    public function getArchive(): ?array {
        return json_decode($this->archive, true);
    }

    /**
     * @param array|null $archive
     */
    public function setArchive(?array $archive): self {
        $this->archive = json_encode($archive);
        return $this;
    }

    /**
     * @return array|null
     */
    public function getProgres(): ?array {
        return json_decode($this->progres, true);
    }

    /**
     * @param array|null $progres
     */
    public function setProgres(?array $progres): self {
        $this->progres = json_encode($progres);
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Deo>
     */
    public function getDeo(): Collection
    {
        return $this->deo;
    }

    public function addDeo(Deo $deo): static
    {
        if (!$this->deo->contains($deo)) {
            $this->deo->add($deo);
            $deo->setProduction($this);
        }

        return $this;
    }

    public function removeDeo(Deo $deo): static
    {
        if ($this->deo->removeElement($deo)) {
            // set the owning side to null (unless already changed)
            if ($deo->getProduction() === $this) {
                $deo->setProduction(null);
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

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProduction($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getProduction() === $this) {
                $task->setProduction(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ManagerChecklist>
     */
    public function getManagerChecklists(): Collection
    {
        return $this->managerChecklists;
    }

    public function addManagerChecklist(ManagerChecklist $managerChecklist): static
    {
        if (!$this->managerChecklists->contains($managerChecklist)) {
            $this->managerChecklists->add($managerChecklist);
            $managerChecklist->setProduction($this);
        }

        return $this;
    }

    public function removeManagerChecklist(ManagerChecklist $managerChecklist): static
    {
        if ($this->managerChecklists->removeElement($managerChecklist)) {
            // set the owning side to null (unless already changed)
            if ($managerChecklist->getProduction() === $this) {
                $managerChecklist->setProduction(null);
            }
        }

        return $this;
    }
}
