<?php

namespace App\Entity;

use App\Repository\FastTaskRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FastTaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
class FastTask {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column]
  private ?int $status = null;

  #[ORM\Column(nullable: true)]
  private ?DateTimeImmutable $datum;

  #[ORM\Column(nullable: true)]
  private ?int $project1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_1 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity1 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description1 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_2 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity2 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description2 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_3 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity3 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description3 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_4 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity4 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description4 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_5 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity5 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description5 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_6 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity6 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description6 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_7 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity7 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description7 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_8 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity8 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description8 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_9 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity9 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description9 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $project10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo1_10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo2_10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $geo3_10 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $activity10 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $description10 = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $oprema10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $status10 = null;

  #[ORM\Column]
  private DateTimeImmutable $created;

  #[ORM\Column]
  private DateTimeImmutable $updated;

  #[ORM\Column(nullable: true)]
  private ?int $noTasks = null;

  #[ORM\Column(nullable: true)]
  private ?int $task1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $task10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $car10 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver1 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver2 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver3 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver4 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver5 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver6 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver7 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver8 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver9 = null;

  #[ORM\Column(nullable: true)]
  private ?int $driver10 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time1 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time2 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time3 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time4 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time5 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time6 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time7 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time8 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time9 = null;

  #[ORM\Column(nullable: true)]
  private ?string $time10 = null;


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

  public function getProject1(): ?int {
    return $this->project1;
  }

  public function setProject1(?int $project1): self {
    $this->project1 = $project1;

    return $this;
  }

  public function getGeo11(): ?int {
    return $this->geo1_1;
  }

  public function setGeo11(?int $geo1_1): self {
    $this->geo1_1 = $geo1_1;

    return $this;
  }

  public function getGeo21(): ?int {
    return $this->geo2_1;
  }

  public function setGeo21(?int $geo2_1): self {
    $this->geo2_1 = $geo2_1;

    return $this;
  }

  public function getGeo31(): ?int {
    return $this->geo3_1;
  }

  public function setGeo31(?int $geo3_1): self {
    $this->geo3_1 = $geo3_1;

    return $this;
  }

  public function getActivity1(): ?array {
    return json_decode($this->activity1, true);
  }

  public function setActivity1(?array $activity1): self {

    if(!is_null($activity1)) {
      $this->activity1 = json_encode($activity1);
    } else {
      $this->activity1 = null;
    }
    return $this;
  }


  public function getDescription1(): ?string {
    return $this->description1;
  }

  public function setDescription1(?string $description1): self {
    $this->description1 = $description1;

    return $this;
  }

  public function getOprema1(): ?array {
    return json_decode($this->oprema1, true);
  }

  public function setOprema1(?array $oprema1): self {
    if(!is_null($oprema1)) {
      $this->oprema1 = json_encode($oprema1);
    } else {
      $this->oprema1 = NULL;
    }
    return $this;
  }

  public function getStatus1(): ?int {
    return $this->status1;
  }

  public function setStatus1(int $status1): self {
    $this->status1 = $status1;

    return $this;
  }

  public function getDatum(): ?\DateTimeImmutable {
    return $this->datum;
  }

  public function setDatum(?\DateTimeImmutable $datum): self {
    $this->datum = $datum;

    return $this;
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
   * @return int|null
   */
  public function getProject2(): ?int {
    return $this->project2;
  }

  /**
   * @param int|null $project2
   */
  public function setProject2(?int $project2): void {
    $this->project2 = $project2;
  }

  /**
   * @return int|null
   */
  public function getGeo12(): ?int {
    return $this->geo1_2;
  }

  /**
   * @param int|null $geo1_2
   */
  public function setGeo12(?int $geo1_2): void {
    $this->geo1_2 = $geo1_2;
  }

  /**
   * @return int|null
   */
  public function getGeo22(): ?int {
    return $this->geo2_2;
  }

  /**
   * @param int|null $geo2_2
   */
  public function setGeo22(?int $geo2_2): void {
    $this->geo2_2 = $geo2_2;
  }

  /**
   * @return int|null
   */
  public function getGeo32(): ?int {
    return $this->geo3_2;
  }

  /**
   * @param int|null $geo3_2
   */
  public function setGeo32(?int $geo3_2): void {
    $this->geo3_2 = $geo3_2;
  }

  public function getActivity2(): ?array {
    return json_decode($this->activity2, true);
  }

  public function setActivity2(?array $activity2): self {
    if(!is_null($activity2)) {
      $this->activity2 = json_encode($activity2);

    } else {
      $this->activity2 = NULL;
    }
    return $this;
  }

  /**
   * @return string|null
   */
  public function getDescription2(): ?string {
    return $this->description2;
  }

  /**
   * @param string|null $description2
   */
  public function setDescription2(?string $description2): void {
    $this->description2 = $description2;
  }


  /**
   * @return int|null
   */
  public function getStatus2(): ?int {
    return $this->status2;
  }

  /**
   * @param int|null $status2
   */
  public function setStatus2(?int $status2): void {
    $this->status2 = $status2;
  }

  /**
   * @return int|null
   */
  public function getProject3(): ?int {
    return $this->project3;
  }

  /**
   * @param int|null $project3
   */
  public function setProject3(?int $project3): void {
    $this->project3 = $project3;
  }

  /**
   * @return int|null
   */
  public function getGeo13(): ?int {
    return $this->geo1_3;
  }

  /**
   * @param int|null $geo1_3
   */
  public function setGeo13(?int $geo1_3): void {
    $this->geo1_3 = $geo1_3;
  }

  /**
   * @return int|null
   */
  public function getGeo23(): ?int {
    return $this->geo2_3;
  }

  /**
   * @param int|null $geo2_3
   */
  public function setGeo23(?int $geo2_3): void {
    $this->geo2_3 = $geo2_3;
  }

  /**
   * @return int|null
   */
  public function getGeo33(): ?int {
    return $this->geo3_3;
  }

  /**
   * @param int|null $geo3_3
   */
  public function setGeo33(?int $geo3_3): void {
    $this->geo3_3 = $geo3_3;
  }

  public function getActivity3(): ?array {
    return json_decode($this->activity3, true);
  }

  public function setActivity3(?array $activity3): self {
    if(!is_null($activity3)) {
      $this->activity3 = json_encode($activity3);

    } else {
      $this->activity3 = NULL;
    }
    return $this;
  }

  /**
   * @return string|null
   */
  public function getDescription3(): ?string {
    return $this->description3;
  }

  /**
   * @param string|null $description3
   */
  public function setDescription3(?string $description3): void {
    $this->description3 = $description3;
  }


  /**
   * @return int|null
   */
  public function getStatus3(): ?int {
    return $this->status3;
  }

  /**
   * @param int|null $status3
   */
  public function setStatus3(?int $status3): void {
    $this->status3 = $status3;
  }

  /**
   * @return int|null
   */
  public function getProject4(): ?int {
    return $this->project4;
  }

  /**
   * @param int|null $project4
   */
  public function setProject4(?int $project4): void {
    $this->project4 = $project4;
  }

  /**
   * @return int|null
   */
  public function getGeo14(): ?int {
    return $this->geo1_4;
  }

  /**
   * @param int|null $geo1_4
   */
  public function setGeo14(?int $geo1_4): void {
    $this->geo1_4 = $geo1_4;
  }

  /**
   * @return int|null
   */
  public function getGeo24(): ?int {
    return $this->geo2_4;
  }

  /**
   * @param int|null $geo2_4
   */
  public function setGeo24(?int $geo2_4): void {
    $this->geo2_4 = $geo2_4;
  }

  /**
   * @return int|null
   */
  public function getGeo34(): ?int {
    return $this->geo3_4;
  }

  /**
   * @param int|null $geo3_4
   */
  public function setGeo34(?int $geo3_4): void {
    $this->geo3_4 = $geo3_4;
  }

  public function getActivity4(): ?array {
    return json_decode($this->activity4, true);
  }


  /**
   * @return string|null
   */
  public function getDescription4(): ?string {
    return $this->description4;
  }

  /**
   * @param string|null $description4
   */
  public function setDescription4(?string $description4): void {
    $this->description4 = $description4;
  }


  /**
   * @return int|null
   */
  public function getStatus4(): ?int {
    return $this->status4;
  }

  /**
   * @param int|null $status4
   */
  public function setStatus4(?int $status4): void {
    $this->status4 = $status4;
  }

  /**
   * @return int|null
   */
  public function getProject5(): ?int {
    return $this->project5;
  }

  /**
   * @param int|null $project5
   */
  public function setProject5(?int $project5): void {
    $this->project5 = $project5;
  }

  /**
   * @return int|null
   */
  public function getGeo15(): ?int {
    return $this->geo1_5;
  }

  /**
   * @param int|null $geo1_5
   */
  public function setGeo15(?int $geo1_5): void {
    $this->geo1_5 = $geo1_5;
  }

  /**
   * @return int|null
   */
  public function getGeo25(): ?int {
    return $this->geo2_5;
  }

  /**
   * @param int|null $geo2_5
   */
  public function setGeo25(?int $geo2_5): void {
    $this->geo2_5 = $geo2_5;
  }

  /**
   * @return int|null
   */
  public function getGeo35(): ?int {
    return $this->geo3_5;
  }

  /**
   * @param int|null $geo3_5
   */
  public function setGeo35(?int $geo3_5): void {
    $this->geo3_5 = $geo3_5;
  }

  public function getActivity5(): ?array {
    return json_decode($this->activity5, true);
  }



  /**
   * @return string|null
   */
  public function getDescription5(): ?string {
    return $this->description5;
  }

  /**
   * @param string|null $description5
   */
  public function setDescription5(?string $description5): void {
    $this->description5 = $description5;
  }

  /**
   * @return int|null
   */
  public function getStatus5(): ?int {
    return $this->status5;
  }

  /**
   * @param int|null $status5
   */
  public function setStatus5(?int $status5): void {
    $this->status5 = $status5;
  }

  /**
   * @return int|null
   */
  public function getProject6(): ?int {
    return $this->project6;
  }

  /**
   * @param int|null $project6
   */
  public function setProject6(?int $project6): void {
    $this->project6 = $project6;
  }

  /**
   * @return int|null
   */
  public function getGeo16(): ?int {
    return $this->geo1_6;
  }

  /**
   * @param int|null $geo1_6
   */
  public function setGeo16(?int $geo1_6): void {
    $this->geo1_6 = $geo1_6;
  }

  /**
   * @return int|null
   */
  public function getGeo26(): ?int {
    return $this->geo2_6;
  }

  /**
   * @param int|null $geo2_6
   */
  public function setGeo26(?int $geo2_6): void {
    $this->geo2_6 = $geo2_6;
  }

  /**
   * @return int|null
   */
  public function getGeo36(): ?int {
    return $this->geo3_6;
  }

  /**
   * @param int|null $geo3_6
   */
  public function setGeo36(?int $geo3_6): void {
    $this->geo3_6 = $geo3_6;
  }

  public function getActivity6(): ?array {
    return json_decode($this->activity6, true);
  }



  /**
   * @return string|null
   */
  public function getDescription6(): ?string {
    return $this->description6;
  }

  /**
   * @param string|null $description6
   */
  public function setDescription6(?string $description6): void {
    $this->description6 = $description6;
  }


  /**
   * @return int|null
   */
  public function getStatus6(): ?int {
    return $this->status6;
  }

  /**
   * @param int|null $status6
   */
  public function setStatus6(?int $status6): void {
    $this->status6 = $status6;
  }

  /**
   * @return int|null
   */
  public function getProject7(): ?int {
    return $this->project7;
  }

  /**
   * @param int|null $project7
   */
  public function setProject7(?int $project7): void {
    $this->project7 = $project7;
  }

  /**
   * @return int|null
   */
  public function getGeo17(): ?int {
    return $this->geo1_7;
  }

  /**
   * @param int|null $geo1_7
   */
  public function setGeo17(?int $geo1_7): void {
    $this->geo1_7 = $geo1_7;
  }

  /**
   * @return int|null
   */
  public function getGeo27(): ?int {
    return $this->geo2_7;
  }

  /**
   * @param int|null $geo2_7
   */
  public function setGeo27(?int $geo2_7): void {
    $this->geo2_7 = $geo2_7;
  }

  /**
   * @return int|null
   */
  public function getGeo37(): ?int {
    return $this->geo3_7;
  }

  /**
   * @param int|null $geo3_7
   */
  public function setGeo37(?int $geo3_7): void {
    $this->geo3_7 = $geo3_7;
  }

  public function getActivity7(): ?array {
    return json_decode($this->activity7, true);
  }



  /**
   * @return string|null
   */
  public function getDescription7(): ?string {
    return $this->description7;
  }

  /**
   * @param string|null $description7
   */
  public function setDescription7(?string $description7): void {
    $this->description7 = $description7;
  }


  /**
   * @return int|null
   */
  public function getStatus7(): ?int {
    return $this->status7;
  }

  /**
   * @param int|null $status7
   */
  public function setStatus7(?int $status7): void {
    $this->status7 = $status7;
  }

  /**
   * @return int|null
   */
  public function getProject8(): ?int {
    return $this->project8;
  }

  /**
   * @param int|null $project8
   */
  public function setProject8(?int $project8): void {
    $this->project8 = $project8;
  }

  /**
   * @return int|null
   */
  public function getGeo18(): ?int {
    return $this->geo1_8;
  }

  /**
   * @param int|null $geo1_8
   */
  public function setGeo18(?int $geo1_8): void {
    $this->geo1_8 = $geo1_8;
  }

  /**
   * @return int|null
   */
  public function getGeo28(): ?int {
    return $this->geo2_8;
  }

  /**
   * @param int|null $geo2_8
   */
  public function setGeo28(?int $geo2_8): void {
    $this->geo2_8 = $geo2_8;
  }

  /**
   * @return int|null
   */
  public function getGeo38(): ?int {
    return $this->geo3_8;
  }

  /**
   * @param int|null $geo3_8
   */
  public function setGeo38(?int $geo3_8): void {
    $this->geo3_8 = $geo3_8;
  }

  public function getActivity8(): ?array {
    return json_decode($this->activity8, true);
  }



  /**
   * @return string|null
   */
  public function getDescription8(): ?string {
    return $this->description8;
  }

  /**
   * @param string|null $description8
   */
  public function setDescription8(?string $description8): void {
    $this->description8 = $description8;
  }


  /**
   * @return int|null
   */
  public function getStatus8(): ?int {
    return $this->status8;
  }

  /**
   * @param int|null $status8
   */
  public function setStatus8(?int $status8): void {
    $this->status8 = $status8;
  }

  /**
   * @return int|null
   */
  public function getProject9(): ?int {
    return $this->project9;
  }

  /**
   * @param int|null $project9
   */
  public function setProject9(?int $project9): void {
    $this->project9 = $project9;
  }

  /**
   * @return int|null
   */
  public function getGeo19(): ?int {
    return $this->geo1_9;
  }

  /**
   * @param int|null $geo1_9
   */
  public function setGeo19(?int $geo1_9): void {
    $this->geo1_9 = $geo1_9;
  }

  /**
   * @return int|null
   */
  public function getGeo29(): ?int {
    return $this->geo2_9;
  }

  /**
   * @param int|null $geo2_9
   */
  public function setGeo29(?int $geo2_9): void {
    $this->geo2_9 = $geo2_9;
  }

  /**
   * @return int|null
   */
  public function getGeo39(): ?int {
    return $this->geo3_9;
  }

  /**
   * @param int|null $geo3_9
   */
  public function setGeo39(?int $geo3_9): void {
    $this->geo3_9 = $geo3_9;
  }

  public function getActivity9(): ?array {
    return json_decode($this->activity9, true);
  }



  /**
   * @return string|null
   */
  public function getDescription9(): ?string {
    return $this->description9;
  }

  /**
   * @param string|null $description9
   */
  public function setDescription9(?string $description9): void {
    $this->description9 = $description9;
  }

  /**
   * @return int|null
   */
  public function getStatus9(): ?int {
    return $this->status9;
  }

  /**
   * @param int|null $status9
   */
  public function setStatus9(?int $status9): void {
    $this->status9 = $status9;
  }

  /**
   * @return int|null
   */
  public function getProject10(): ?int {
    return $this->project10;
  }

  /**
   * @param int|null $project10
   */
  public function setProject10(?int $project10): void {
    $this->project10 = $project10;
  }

  /**
   * @return int|null
   */
  public function getGeo110(): ?int {
    return $this->geo1_10;
  }

  /**
   * @param int|null $geo1_10
   */
  public function setGeo110(?int $geo1_10): void {
    $this->geo1_10 = $geo1_10;
  }

  /**
   * @return int|null
   */
  public function getGeo210(): ?int {
    return $this->geo2_10;
  }

  /**
   * @param int|null $geo2_10
   */
  public function setGeo210(?int $geo2_10): void {
    $this->geo2_10 = $geo2_10;
  }

  /**
   * @return int|null
   */
  public function getGeo310(): ?int {
    return $this->geo3_10;
  }

  /**
   * @param int|null $geo3_10
   */
  public function setGeo310(?int $geo3_10): void {
    $this->geo3_10 = $geo3_10;
  }

  public function getActivity10(): ?array {
    return json_decode($this->activity10, true);
  }


  /**
   * @return string|null
   */
  public function getDescription10(): ?string {
    return $this->description10;
  }

  /**
   * @param string|null $description10
   */
  public function setDescription10(?string $description10): void {
    $this->description10 = $description10;
  }


  /**
   * @return int|null
   */
  public function getStatus10(): ?int {
    return $this->status10;
  }

  /**
   * @param int|null $status10
   */
  public function setStatus10(?int $status10): void {
    $this->status10 = $status10;
  }

  /**
   * @return int|null
   */
  public function getStatus(): ?int {
    return $this->status;
  }

  /**
   * @param int|null $status
   */
  public function setStatus(?int $status): void {
    $this->status = $status;
  }

  public function getNoTasks(): ?int
  {
      return $this->noTasks;
  }

  public function setNoTasks(?int $noTasks): self
  {
      $this->noTasks = $noTasks;

      return $this;
  }


  public function getCar1(): ?int
  {
      return $this->car1;
  }

  public function setCar1(?int $car1): self
  {
      $this->car1 = $car1;

      return $this;
  }

  /**
   * @return int|null
   */
  public function getCar2(): ?int {
    return $this->car2;
  }

  /**
   * @param int|null $car2
   */
  public function setCar2(?int $car2): void {
    $this->car2 = $car2;
  }

  /**
   * @return int|null
   */
  public function getCar3(): ?int {
    return $this->car3;
  }

  /**
   * @param int|null $car3
   */
  public function setCar3(?int $car3): void {
    $this->car3 = $car3;
  }

  /**
   * @return int|null
   */
  public function getCar4(): ?int {
    return $this->car4;
  }

  /**
   * @param int|null $car4
   */
  public function setCar4(?int $car4): void {
    $this->car4 = $car4;
  }

  /**
   * @return int|null
   */
  public function getCar5(): ?int {
    return $this->car5;
  }

  /**
   * @param int|null $car5
   */
  public function setCar5(?int $car5): void {
    $this->car5 = $car5;
  }

  /**
   * @return int|null
   */
  public function getCar6(): ?int {
    return $this->car6;
  }

  /**
   * @param int|null $car6
   */
  public function setCar6(?int $car6): void {
    $this->car6 = $car6;
  }

  /**
   * @return int|null
   */
  public function getCar7(): ?int {
    return $this->car7;
  }

  /**
   * @param int|null $car7
   */
  public function setCar7(?int $car7): void {
    $this->car7 = $car7;
  }

  /**
   * @return int|null
   */
  public function getCar8(): ?int {
    return $this->car8;
  }

  /**
   * @param int|null $car8
   */
  public function setCar8(?int $car8): void {
    $this->car8 = $car8;
  }

  /**
   * @return int|null
   */
  public function getCar9(): ?int {
    return $this->car9;
  }

  /**
   * @param int|null $car9
   */
  public function setCar9(?int $car9): void {
    $this->car9 = $car9;
  }

  /**
   * @return int|null
   */
  public function getCar10(): ?int {
    return $this->car10;
  }

  /**
   * @param int|null $car10
   */
  public function setCar10(?int $car10): void {
    $this->car10 = $car10;
  }

  /**
   * @return int|null
   */
  public function getDriver1(): ?int {
    return $this->driver1;
  }

  /**
   * @param int|null $driver1
   */
  public function setDriver1(?int $driver1): void {
    $this->driver1 = $driver1;
  }

  /**
   * @return int|null
   */
  public function getDriver2(): ?int {
    return $this->driver2;
  }

  /**
   * @param int|null $driver2
   */
  public function setDriver2(?int $driver2): void {
    $this->driver2 = $driver2;
  }

  /**
   * @return int|null
   */
  public function getDriver3(): ?int {
    return $this->driver3;
  }

  /**
   * @param int|null $driver3
   */
  public function setDriver3(?int $driver3): void {
    $this->driver3 = $driver3;
  }

  /**
   * @return int|null
   */
  public function getDriver4(): ?int {
    return $this->driver4;
  }

  /**
   * @param int|null $driver4
   */
  public function setDriver4(?int $driver4): void {
    $this->driver4 = $driver4;
  }

  /**
   * @return int|null
   */
  public function getDriver5(): ?int {
    return $this->driver5;
  }

  /**
   * @param int|null $driver5
   */
  public function setDriver5(?int $driver5): void {
    $this->driver5 = $driver5;
  }

  /**
   * @return int|null
   */
  public function getDriver6(): ?int {
    return $this->driver6;
  }

  /**
   * @param int|null $driver6
   */
  public function setDriver6(?int $driver6): void {
    $this->driver6 = $driver6;
  }

  /**
   * @return int|null
   */
  public function getDriver7(): ?int {
    return $this->driver7;
  }

  /**
   * @param int|null $driver7
   */
  public function setDriver7(?int $driver7): void {
    $this->driver7 = $driver7;
  }

  /**
   * @return int|null
   */
  public function getDriver8(): ?int {
    return $this->driver8;
  }

  /**
   * @param int|null $driver8
   */
  public function setDriver8(?int $driver8): void {
    $this->driver8 = $driver8;
  }

  /**
   * @return int|null
   */
  public function getDriver9(): ?int {
    return $this->driver9;
  }

  /**
   * @param int|null $driver9
   */
  public function setDriver9(?int $driver9): void {
    $this->driver9 = $driver9;
  }

  /**
   * @return int|null
   */
  public function getDriver10(): ?int {
    return $this->driver10;
  }

  /**
   * @param int|null $driver10
   */
  public function setDriver10(?int $driver10): void {
    $this->driver10 = $driver10;
  }



  /**
   * @return string|null
   */
  public function getTime1(): ?string {
    return $this->time1;
  }

  /**
   * @param string|null $time1
   */
  public function setTime1(?string $time1): void {
    $this->time1 = $time1;
  }

  /**
   * @return string|null
   */
  public function getTime2(): ?string {
    return $this->time2;
  }

  /**
   * @param string|null $time2
   */
  public function setTime2(?string $time2): void {
    $this->time2 = $time2;
  }

  /**
   * @return string|null
   */
  public function getTime3(): ?string {
    return $this->time3;
  }

  /**
   * @param string|null $time3
   */
  public function setTime3(?string $time3): void {
    $this->time3 = $time3;
  }

  /**
   * @return string|null
   */
  public function getTime4(): ?string {
    return $this->time4;
  }

  /**
   * @param string|null $time4
   */
  public function setTime4(?string $time4): void {
    $this->time4 = $time4;
  }

  /**
   * @return string|null
   */
  public function getTime5(): ?string {
    return $this->time5;
  }

  /**
   * @param string|null $time5
   */
  public function setTime5(?string $time5): void {
    $this->time5 = $time5;
  }

  /**
   * @return string|null
   */
  public function getTime6(): ?string {
    return $this->time6;
  }

  /**
   * @param string|null $time6
   */
  public function setTime6(?string $time6): void {
    $this->time6 = $time6;
  }

  /**
   * @return string|null
   */
  public function getTime7(): ?string {
    return $this->time7;
  }

  /**
   * @param string|null $time7
   */
  public function setTime7(?string $time7): void {
    $this->time7 = $time7;
  }

  /**
   * @return string|null
   */
  public function getTime8(): ?string {
    return $this->time8;
  }

  /**
   * @param string|null $time8
   */
  public function setTime8(?string $time8): void {
    $this->time8 = $time8;
  }

  /**
   * @return string|null
   */
  public function getTime9(): ?string {
    return $this->time9;
  }

  /**
   * @param string|null $time9
   */
  public function setTime9(?string $time9): void {
    $this->time9 = $time9;
  }

  /**
   * @return string|null
   */
  public function getTime10(): ?string {
    return $this->time10;
  }

  /**
   * @param string|null $time10
   */
  public function setTime10(?string $time10): void {
    $this->time10 = $time10;
  }

  public function getOprema2(): ?array {
    return json_decode($this->oprema2, true);
  }

  public function setOprema2(?array $oprema2): self {
    if(!is_null($oprema2)) {
      $this->oprema2 = json_encode($oprema2);

    } else {
      $this->oprema2 = NULL;
    }
    return $this;
  }

  public function getOprema3(): ?array {
    return json_decode($this->oprema3, true);
  }

  public function setOprema3(?array $oprema3): self {
    if(!is_null($oprema3)) {
      $this->oprema3 = json_encode($oprema3);

    } else {
      $this->oprema3 = NULL;
    }
    return $this;
  }
  public function getOprema4(): ?array {
    return json_decode($this->oprema4, true);
  }

  public function setOprema4(?array $oprema4): self {
    if(!is_null($oprema4)) {
      $this->oprema4 = json_encode($oprema4);

    } else {
      $this->oprema4 = NULL;
    }
    return $this;
  }
  public function getOprema5(): ?array {
    return json_decode($this->oprema5, true);
  }

  public function setOprema5(?array $oprema5): self {
    if(!is_null($oprema5)) {
      $this->oprema5= json_encode($oprema5);

    } else {
      $this->oprema5 = NULL;
    }
    return $this;
  }
  public function getOprema6(): ?array {
    return json_decode($this->oprema6, true);
  }

  public function setOprema6(?array $oprema6): self {
    if(!is_null($oprema6)) {
      $this->oprema6 = json_encode($oprema6);

    } else {
      $this->oprema6 = NULL;
    }
    return $this;
  }
  public function getOprema7(): ?array {
    return json_decode($this->oprema7, true);
  }

  public function setOprema7(?array $oprema7): self {
    if(!is_null($oprema7)) {
      $this->oprema7 = json_encode($oprema7);

    } else {
      $this->oprema7 = NULL;
    }
    return $this;
  }
  public function getOprema8(): ?array {
    return json_decode($this->oprema8, true);
  }

  public function setOprema8(?array $oprema8): self {
    if(!is_null($oprema8)) {
      $this->oprema8 = json_encode($oprema8);

    } else {
      $this->oprema8 = NULL;
    }
    return $this;
  }
  public function getOprema9(): ?array {
    return json_decode($this->oprema9, true);
  }

  public function setOprema9(?array $oprema9): self {
    if(!is_null($oprema9)) {
      $this->oprema9 = json_encode($oprema9);

    } else {
      $this->oprema9 = NULL;
    }
    return $this;
  }
  public function getOprema10(): ?array {
    return json_decode($this->oprema10, true);
  }

  public function setOprema10(?array $oprema10): self {
    if(!is_null($oprema10)) {
      $this->oprema10 = json_encode($oprema10);

    } else {
      $this->oprema10 = NULL;
    }
    return $this;
  }

  public function setActivity4(?array $activity4): self {
    if(!is_null($activity4)) {
      $this->activity4 = json_encode($activity4);

    } else {
      $this->activity4 = NULL;
    }
    return $this;
  }

  public function setActivity5(?array $activity5): self {
    if(!is_null($activity5)) {
      $this->activity5 = json_encode($activity5);

    } else {
      $this->activity5 = NULL;
    }
    return $this;
  }

  public function setActivity6(?array $activity6): self {
    if(!is_null($activity6)) {
      $this->activity6 = json_encode($activity6);

    } else {
      $this->activity6 = NULL;
    }
    return $this;
  }

  public function setActivity7(?array $activity7): self {
    if(!is_null($activity7)) {
      $this->activity7 = json_encode($activity7);

    } else {
      $this->activity7 = NULL;
    }
    return $this;
  }

  public function setActivity8(?array $activity8): self {
    if(!is_null($activity8)) {
      $this->activity8 = json_encode($activity8);

    } else {
      $this->activity8 = NULL;
    }
    return $this;
  }

  public function setActivity9(?array $activity9): self {
    if(!is_null($activity9)) {
      $this->activity9 = json_encode($activity9);

    } else {
      $this->activity9 = NULL;
    }
    return $this;
  }

  public function setActivity10(?array $activity10): self {
    if(!is_null($activity10)) {
      $this->activity10 = json_encode($activity10);

    } else {
      $this->activity10 = NULL;
    }
    return $this;
  }

  /**
   * @return int|null
   */
  public function getTask1(): ?int {
    return $this->task1;
  }

  /**
   * @param int|null $task1
   */
  public function setTask1(?int $task1): void {
    $this->task1 = $task1;
  }

  /**
   * @return int|null
   */
  public function getTask2(): ?int {
    return $this->task2;
  }

  /**
   * @param int|null $task2
   */
  public function setTask2(?int $task2): void {
    $this->task2 = $task2;
  }

  /**
   * @return int|null
   */
  public function getTask3(): ?int {
    return $this->task3;
  }

  /**
   * @param int|null $task3
   */
  public function setTask3(?int $task3): void {
    $this->task3 = $task3;
  }

  /**
   * @return int|null
   */
  public function getTask4(): ?int {
    return $this->task4;
  }

  /**
   * @param int|null $task4
   */
  public function setTask4(?int $task4): void {
    $this->task4 = $task4;
  }

  /**
   * @return int|null
   */
  public function getTask5(): ?int {
    return $this->task5;
  }

  /**
   * @param int|null $task5
   */
  public function setTask5(?int $task5): void {
    $this->task5 = $task5;
  }

  /**
   * @return int|null
   */
  public function getTask6(): ?int {
    return $this->task6;
  }

  /**
   * @param int|null $task6
   */
  public function setTask6(?int $task6): void {
    $this->task6 = $task6;
  }

  /**
   * @return int|null
   */
  public function getTask7(): ?int {
    return $this->task7;
  }

  /**
   * @param int|null $task7
   */
  public function setTask7(?int $task7): void {
    $this->task7 = $task7;
  }

  /**
   * @return int|null
   */
  public function getTask8(): ?int {
    return $this->task8;
  }

  /**
   * @param int|null $task8
   */
  public function setTask8(?int $task8): void {
    $this->task8 = $task8;
  }

  /**
   * @return int|null
   */
  public function getTask9(): ?int {
    return $this->task9;
  }

  /**
   * @param int|null $task9
   */
  public function setTask9(?int $task9): void {
    $this->task9 = $task9;
  }

  /**
   * @return int|null
   */
  public function getTask10(): ?int {
    return $this->task10;
  }

  /**
   * @param int|null $task10
   */
  public function setTask10(?int $task10): void {
    $this->task10 = $task10;
  }


}
