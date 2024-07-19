<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Plan;
use App\Entity\PripremaZadatak;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Tool;
use App\Entity\ToolReservation;
use App\Entity\User;
use App\Service\MailService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Plan>
 *
 * @method Plan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Plan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Plan[]    findAll()
 * @method Plan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanRepository extends ServiceEntityRepository {
  private $mail;
  private $security;
  public function __construct(ManagerRegistry $registry, MailService $mail, Security $security) {
    parent::__construct($registry, Plan::class);
    $this->mail = $mail;
    $this->security = $security;
  }

  public function save(Plan $plan): Plan {
    if (is_null($plan->getId())) {
      $this->getEntityManager()->persist($plan);
    }

    $this->getEntityManager()->flush();
    return $plan;
  }

  public function savePlan(Plan $plan, $data, $dataInterni): Plan {

    foreach ($data as $dat) {
      $users = new ArrayCollection();
      $activities = new ArrayCollection();
      $tools = new ArrayCollection();
      $priprema = new PripremaZadatak();

      $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
      $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
      foreach ($dat['users'] as $user) {
        $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
      }
      $priprema->setAssignedUsers($users);
      $priprema->setPriorityUserLog($dat['primary']);

      if (isset($dat['activity'])) {
        foreach ($dat['activity'] as $act) {
          $activities->add($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
        $priprema->setActivity($activities);
      }

      if (isset($dat['tools'])) {
        foreach ($dat['tools'] as $tool) {
          $tools->add($this->getEntityManager()->getRepository(Tool::class)->find($tool));
        }
        $priprema->setOprema($tools);
      }

      if (isset($dat['naplativ'])) {
        $priprema->setIsFree(false);
      } else {
        $priprema->setIsFree(true);
      }

      if (!empty($dat['car'])) {
        $priprema->setCar($dat['car']);
        $priprema->setDriver($dat['driver']);
      }

      $priprema->setDescription($dat['desc']);

      $vreme = $plan->getDatumKreiranja();
      if (!empty($dat['vreme'])) {
        $novo = $dat['vreme'];
        $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
        $vreme = new DateTimeImmutable("$dateString $novo");
      }
      $priprema->setVreme($vreme);
      $priprema->setTaskType(1);
      $plan->addPripremaZadatak($priprema);
    }
    foreach ($dataInterni as $dat) {
      $users = new ArrayCollection();

      $priprema = new PripremaZadatak();

      $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
      $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
      foreach ($dat['users'] as $user) {
        $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
      }
      $priprema->setAssignedUsers($users);

      $priprema->setDescription($dat['desc']);

      $vreme = $plan->getDatumKreiranja();
      if (!empty($dat['vreme'])) {
        $novo = $dat['vreme'];
        $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
        $vreme = new DateTimeImmutable("$dateString $novo");
      }
      $priprema->setVreme($vreme);
      $priprema->setTaskType(2);
      $plan->addPripremaZadatak($priprema);
    }

    if (is_null($plan->getId())) {
      $plan->setCreatedBy($this->security->getUser());
    } else {
      $plan->setEditBy($this->security->getUser());
    }

    $this->save($plan);
    return $plan;
  }

  public function editPlan(Plan $plan, $data, $dataInterni): Plan {

    $ostaju = [];

    $noviNiz = array_merge($data, $dataInterni);

    foreach ($noviNiz as $task) {
      if (isset($task['id'])) {
       $ostaju[] = $task['id'];
      }
    }
    $ispadaju = $this->getEntityManager()->getRepository(PripremaZadatak::class)->findBy(['plan' => $plan]);

//    $workTime = $plan->getCompany()->getSettings()->getRadnoVreme();
//    $presek = $plan->getDatumKreiranja();
//    $danPre = $presek->sub(new DateInterval('P1D'));
//    $danPreSaRadnimVremenom = $danPre->setTime((int)$workTime->format('H'), (int)$workTime->format('i'));
//    $trenutnoVreme = new DateTimeImmutable('now');

    if ($plan->getStatus() == FastTaskData::SAVED) {

      foreach ($ispadaju as $isp) {
        if (!in_array($isp->getId(), $ostaju)) {
          $plan->removePripremaZadatak($isp);
          $plan->setStatus(FastTaskData::EDIT);
        }
      }

      foreach ($data as $dat) {

        if (isset($dat['id'])) {
          $users = new ArrayCollection();
          $activities = new ArrayCollection();
          $tools = new ArrayCollection();

          $priprema = $this->getEntityManager()->getRepository(PripremaZadatak::class)->find($dat['id']);
          if ($priprema->getProject()->getId() != $dat['projekat']) {
            $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }
          if ($priprema->getCategory()->getId() != $dat['category']) {
            $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }
          if ($priprema->getPriorityUserLog() != $dat['primary']) {
            $priprema->setPriorityUserLog($dat['primary']);
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }
          if (trim($priprema->getDescription()) != trim($dat['desc'])) {
            $priprema->setDescription(trim($dat['desc']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }

          if (!empty($dat['car'])) {
            if ($priprema->getCar() != $dat['car']) {
              $priprema->setCar($dat['car']);
              $priprema->setDriver($dat['driver']);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          } else {
            if (!is_null($priprema->getCar())) {
              $priprema->setCar(null);
              $priprema->setDriver(null);
              $plan->setStatus(FastTaskData::EDIT);
            }
          }

          $assignedUsersIds = $priprema->getAssignedUsersIds();

          if (!empty(array_diff($assignedUsersIds, $dat['users'])) || !empty(array_diff($dat['users'], $assignedUsersIds))) {
            $priprema->setAssignedUsers($users);
            foreach ($dat['users'] as $user) {
              $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
            }
            $priprema->setAssignedUsers($users);
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }


          if (isset($dat['activity'])) {
            $activitiesIds = $priprema->getActivitiesIds();
            if (!empty(array_diff($activitiesIds, $dat['activity'])) || !empty(array_diff($dat['activity'], $activitiesIds))) {
              $priprema->setActivity($activities);
              foreach ($dat['activity'] as $act) {
                $activities->add($this->getEntityManager()->getRepository(Activity::class)->find($act));
              }
              $priprema->setActivity($activities);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          } else {
            if (!empty($priprema->getActivitiesIds())) {
              $priprema->setActivity($activities);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          }

          if (isset($dat['tools'])) {
            $toolsIds = $priprema->getOpremaIds();
            if (!empty(array_diff($toolsIds, $dat['tools'])) || !empty(array_diff($dat['tools'], $toolsIds))) {
              $priprema->setOprema($tools);
              foreach ($dat['tools'] as $tool) {
                $tools->add($this->getEntityManager()->getRepository(Tool::class)->find($tool));
              }
              $priprema->setOprema($tools);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          } else {
            if (!empty($priprema->getOpremaIds())) {
              $priprema->setOprema($tools);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          }

          if (isset($dat['naplativ'])) {
            if ($priprema->getIsFree() == 1) {
              $priprema->setIsFree(false);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          } else {
            if ($priprema->getIsFree() == 0) {
              $priprema->setIsFree(true);
              $priprema->setStatus(2);
              $plan->setStatus(FastTaskData::EDIT);
            }
          }
          $this->getEntityManager()->getRepository(PripremaZadatak::class)->save($priprema);
        } else {

          $plan->setStatus(FastTaskData::EDIT);
          $users = new ArrayCollection();
          $activities = new ArrayCollection();
          $tools = new ArrayCollection();
          $priprema = new PripremaZadatak();

          $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          foreach ($dat['users'] as $user) {
            $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
          }
          $priprema->setAssignedUsers($users);
          $priprema->setPriorityUserLog($dat['primary']);

          if (isset($dat['activity'])) {
            foreach ($dat['activity'] as $act) {
              $activities->add($this->getEntityManager()->getRepository(Activity::class)->find($act));
            }
            $priprema->setActivity($activities);
          }

          if (isset($dat['tools'])) {
            foreach ($dat['tools'] as $tool) {
              $tools->add($this->getEntityManager()->getRepository(Tool::class)->find($tool));
            }
            $priprema->setOprema($tools);
          }

          if (isset($dat['naplativ'])) {
            $priprema->setIsFree(false);
          } else {
            $priprema->setIsFree(true);
          }

          if (!empty($dat['car'])) {
            $priprema->setCar($dat['car']);
            $priprema->setDriver($dat['driver']);
          }

          $priprema->setDescription($dat['desc']);

          $vreme = $plan->getDatumKreiranja();
          if (!empty($dat['vreme'])) {
            $novo = $dat['vreme'];
            $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
            $vreme = new DateTimeImmutable("$dateString $novo");
          }
          $priprema->setVreme($vreme);
          $priprema->setTaskType(1);
          $plan->addPripremaZadatak($priprema);
        }
      }

      foreach ($dataInterni as $dat) {

        if (isset($dat['id'])) {

          $users = new ArrayCollection();
          $priprema = $this->getEntityManager()->getRepository(PripremaZadatak::class)->find($dat['id']);

          if ($priprema->getProject()->getId() != $dat['projekat']) {
            $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }
          if ($priprema->getCategory()->getId() != $dat['category']) {
            $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }

          if (trim($priprema->getDescription()) != trim($dat['desc'])) {
            $priprema->setDescription(trim($dat['desc']));
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }

          $assignedUsersIds = $priprema->getAssignedUsersIds();

          if (!empty(array_diff($assignedUsersIds, $dat['users'])) || !empty(array_diff($dat['users'], $assignedUsersIds))) {
            $priprema->setAssignedUsers($users);
            foreach ($dat['users'] as $user) {
              $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
            }
            $priprema->setAssignedUsers($users);
            $priprema->setStatus(2);
            $plan->setStatus(FastTaskData::EDIT);
          }

          $this->getEntityManager()->getRepository(PripremaZadatak::class)->save($priprema);
        } else {
          $plan->setStatus(FastTaskData::EDIT);
          $users = new ArrayCollection();
          $priprema = new PripremaZadatak();

          $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          foreach ($dat['users'] as $user) {
            $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
          }
          $priprema->setAssignedUsers($users);

          $priprema->setDescription($dat['desc']);

          $vreme = $plan->getDatumKreiranja();
          if (!empty($dat['vreme'])) {
            $novo = $dat['vreme'];
            $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
            $vreme = new DateTimeImmutable("$dateString $novo");
          }
          $priprema->setVreme($vreme);
          $priprema->setTaskType(2);
          $plan->addPripremaZadatak($priprema);
        }
      }
    } else {

      foreach ($ispadaju as $isp) {
        if (!in_array($isp->getId(), $ostaju)) {
          $plan->removePripremaZadatak($isp);
        }
      }

      foreach ($data as $dat) {

        if (isset($dat['id'])) {
          $users = new ArrayCollection();
          $activities = new ArrayCollection();
          $tools = new ArrayCollection();

          $priprema = $this->getEntityManager()->getRepository(PripremaZadatak::class)->find($dat['id']);
          if ($priprema->getProject()->getId() != $dat['projekat']) {
            $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          }
          if ($priprema->getCategory()->getId() != $dat['category']) {
            $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          }
          if ($priprema->getPriorityUserLog() != $dat['primary']) {
            $priprema->setPriorityUserLog($dat['primary']);
          }
          if (trim($priprema->getDescription()) != trim($dat['desc'])) {
            $priprema->setDescription(trim($dat['desc']));
          }

          if (!empty($dat['car'])) {
            if ($priprema->getCar() != $dat['car']) {
              $priprema->setCar($dat['car']);
              $priprema->setDriver($dat['driver']);
            }
          } else {
            if (!is_null($priprema->getCar())) {
              $priprema->setCar(null);
              $priprema->setDriver(null);
            }
          }

          $assignedUsersIds = $priprema->getAssignedUsersIds();

          if (!empty(array_diff($assignedUsersIds, $dat['users'])) || !empty(array_diff($dat['users'], $assignedUsersIds))) {
            $priprema->setAssignedUsers($users);
            foreach ($dat['users'] as $user) {
              $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
            }
            $priprema->setAssignedUsers($users);
          }


          if (isset($dat['activity'])) {
            $activitiesIds = $priprema->getActivitiesIds();
            if (!empty(array_diff($activitiesIds, $dat['activity'])) || !empty(array_diff($dat['activity'], $activitiesIds))) {
              $priprema->setActivity($activities);
              foreach ($dat['activity'] as $act) {
                $activities->add($this->getEntityManager()->getRepository(Activity::class)->find($act));
              }
              $priprema->setActivity($activities);
            }
          } else {
            if (!empty($priprema->getActivitiesIds())) {
              $priprema->setActivity($activities);
            }
          }

          if (isset($dat['tools'])) {
            $toolsIds = $priprema->getOpremaIds();
            if (!empty(array_diff($toolsIds, $dat['tools'])) || !empty(array_diff($dat['tools'], $toolsIds))) {
              $priprema->setOprema($tools);
              foreach ($dat['tools'] as $tool) {
                $tools->add($this->getEntityManager()->getRepository(Tool::class)->find($tool));
              }
              $priprema->setOprema($tools);
            }
          } else {
            if (!empty($priprema->getOpremaIds())) {
              $priprema->setOprema($tools);
            }
          }

          if (isset($dat['naplativ'])) {
            if ($priprema->getIsFree() == 1) {
              $priprema->setIsFree(false);
            }
          } else {
            if ($priprema->getIsFree() == 0) {
              $priprema->setIsFree(true);
            }
          }
          $this->getEntityManager()->getRepository(PripremaZadatak::class)->save($priprema);
        } else {

          $users = new ArrayCollection();
          $activities = new ArrayCollection();
          $tools = new ArrayCollection();
          $priprema = new PripremaZadatak();

          $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          foreach ($dat['users'] as $user) {
            $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
          }
          $priprema->setAssignedUsers($users);
          $priprema->setPriorityUserLog($dat['primary']);

          if (isset($dat['activity'])) {
            foreach ($dat['activity'] as $act) {
              $activities->add($this->getEntityManager()->getRepository(Activity::class)->find($act));
            }
            $priprema->setActivity($activities);
          }

          if (isset($dat['tools'])) {
            foreach ($dat['tools'] as $tool) {
              $tools->add($this->getEntityManager()->getRepository(Tool::class)->find($tool));
            }
            $priprema->setOprema($tools);
          }

          if (isset($dat['naplativ'])) {
            $priprema->setIsFree(false);
          } else {
            $priprema->setIsFree(true);
          }

          if (!empty($dat['car'])) {
            $priprema->setCar($dat['car']);
            $priprema->setDriver($dat['driver']);
          }

          $priprema->setDescription($dat['desc']);

          $vreme = $plan->getDatumKreiranja();
          if (!empty($dat['vreme'])) {
            $novo = $dat['vreme'];
            $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
            $vreme = new DateTimeImmutable("$dateString $novo");
          }
          $priprema->setVreme($vreme);
          $priprema->setTaskType(1);
          $plan->addPripremaZadatak($priprema);
        }
      }

      foreach ($dataInterni as $dat) {

        if (isset($dat['id'])) {

          $users = new ArrayCollection();
          $priprema = $this->getEntityManager()->getRepository(PripremaZadatak::class)->find($dat['id']);

          if ($priprema->getProject()->getId() != $dat['projekat']) {
            $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          }
          if ($priprema->getCategory()->getId() != $dat['category']) {
            $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          }

          if (trim($priprema->getDescription()) != trim($dat['desc'])) {
            $priprema->setDescription(trim($dat['desc']));
          }

          $assignedUsersIds = $priprema->getAssignedUsersIds();

          if (!empty(array_diff($assignedUsersIds, $dat['users'])) || !empty(array_diff($dat['users'], $assignedUsersIds))) {
            $priprema->setAssignedUsers($users);
            foreach ($dat['users'] as $user) {
              $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
            }
            $priprema->setAssignedUsers($users);
          }

          $this->getEntityManager()->getRepository(PripremaZadatak::class)->save($priprema);
        } else {

          $users = new ArrayCollection();
          $priprema = new PripremaZadatak();

          $priprema->setProject($this->getEntityManager()->getRepository(Project::class)->find($dat['projekat']));
          $priprema->setCategory($this->getEntityManager()->getRepository(Category::class)->find($dat['category']));
          foreach ($dat['users'] as $user) {
            $users->add($this->getEntityManager()->getRepository(User::class)->find($user));
          }
          $priprema->setAssignedUsers($users);

          $priprema->setDescription($dat['desc']);

          $vreme = $plan->getDatumKreiranja();
          if (!empty($dat['vreme'])) {
            $novo = $dat['vreme'];
            $dateString = $plan->getDatumKreiranja()->format('Y-m-d');
            $vreme = new DateTimeImmutable("$dateString $novo");
          }
          $priprema->setVreme($vreme);
          $priprema->setTaskType(2);
          $plan->addPripremaZadatak($priprema);
        }
      }
    }

    $plan->setEditBy($this->security->getUser());

    $this->save($plan);
    return $plan;
  }

  public function remove(Plan $plan): void {
    $this->getEntityManager()->remove($plan);
    $this->getEntityManager()->flush();
  }

  public function getUsersForEmail(Plan $plan, int $status): array {

    $users = [];
    if ($status == FastTaskData::EDIT) {
      foreach ($plan->getPripremaZadataks() as $task) {
        if ($task->getStatus() == 2) {
          foreach ($task->getAssignedUsersIds() as $user) {
            $users[] = $user;
          }
        }
      }
    } else {
      foreach ($plan->getPripremaZadataks() as $task) {
        foreach ($task->getAssignedUsersIds() as $user) {
          $users[] = $user;
        }
      }
    }

    $users = array_filter(array_unique($users));

    $usersList = [];
    if (!empty($users)) {
      foreach ($users as $usr) {
        $usersList[] = $this->getEntityManager()->getRepository(User::class)->find($usr);
      }
    }

    return $usersList;
  }

  public function getTimeTableFinishCommand(DateTimeImmutable $date, Company $company): array {

    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('f');
    $qb
      ->where($qb->expr()->between('f.datumKreiranja', ':start', ':end'))
      ->andWhere('f.status = :status1 OR f.status = :status2')
      ->andWhere('f.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter('status1', FastTaskData::SAVED)
      ->setParameter('status2', FastTaskData::EDIT)
      ->setMaxResults(1);

    $query = $qb->getQuery();
    return $query->getResult();


  }

  public function getAllPlansPaginator() {
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('f');
    $qb
      ->where('f.company = :company')
      ->setParameter(':company', $company)
      ->orderBy('f.datumKreiranja', 'DESC');
    return $qb->getQuery();

  }

  public function getDisabledDates(): array {
    $company = $this->security->getUser()->getCompany();
    $dates = [];
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere($qb->expr()->orX(
        $qb->expr()->eq('f.status', ':status2'),
        $qb->expr()->eq('f.status', ':status3'),
        $qb->expr()->eq('f.status', ':status4')
      ))
      ->andWhere('f.company = :company')
      ->setParameter('company', $company)
      ->setParameter('status2', FastTaskData::OPEN)
      ->setParameter('status3', FastTaskData::SAVED)
      ->setParameter('status4', FastTaskData::EDIT);

    $query = $qb->getQuery();
    $fastTasks = $query->getResult();

    if (!empty($fastTasks)) {
      foreach ($fastTasks as $task) {
        $dates[] = $task->getDatumKreiranja()->format('d.m.Y');
      }
    }
    return $dates;
  }

  public function findForForm(int $id = 0): Plan {

    if (empty($id)) {
      $task = new Plan();
      $task->setCompany($this->security->getUser()->getCompany());

      return $task;
    }
    return $this->getEntityManager()->getRepository(Plan::class)->find($id);

  }

  public function findCarToReserve(User $user): ?Car {
    $company = $this->security->getUser()->getCompany();
    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);


    $lista = [];
    foreach ($danasnjiTaskovi as $dnsTask) {
      if (!empty($dnsTask['driver']) && $dnsTask['driver'][0] == $user ){
        if ($dnsTask['status'] != 2) {
          $lista[] = [
            'datum' => $dnsTask['task']->getTime(),
            'car' => $dnsTask['task']->getCar(),
          ];
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if (!empty($dnsTask['driver']) && $dnsTask['driver'][0] == $user ){
          if ($dnsTask['status'] != 2) {
            $lista[] = [
              'datum' => $dnsTask['task']->getTime(),
              'car' => $dnsTask['task']->getCar(),
            ];
          }
        }
      }
      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))

        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if(!is_null($fastTask)) {
        foreach ($fastTask->getPripremaZadataks() as $fast) {
          if (!is_null($fast->getDriver())) {
            if ($fast->getDriver() == $user->getId()) {
              if (!is_null($fast->getVreme())) {
                $vreme = $fastTask->getDatumKreiranja();
              } else {
                $vreme = $fast->getVreme();
              }

              if (!is_null($fast->getCar())) {
                $vozilo = $fast->getCar();
              } else {
                $vozilo = null;
              }
              $lista[] = [
                'datum' => $vreme,
                'car' => $vozilo,
              ];
            }
          }
        }
      }
    }

    usort($lista, function($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['car'])) {
        return $this->getEntityManager()->getRepository(Car::class)->find($list['car']);
      }
    }

    return null;

  }
  public function whereCarShouldGo(Car $car): ?User {
    $company = $this->security->getUser()->getCompany();

    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];
    foreach ($danasnjiTaskovi as $dnsTask) {
      if (!empty($dnsTask['car']) && $dnsTask['car'][0] == $car ){
        if ($dnsTask['status'] != 2) {
          $lista[] = [
            'datum' => $dnsTask['task']->getTime(),
            'driver' => $dnsTask['task']->getDriver(),
          ];
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if (!empty($dnsTask['car']) && $dnsTask['car'][0] == $car ){
          if ($dnsTask['status'] != 2) {
            $lista[] = [
              'datum' => $dnsTask['task']->getTime(),
              'driver' => $dnsTask['task']->getDriver(),
            ];
          }
        }
      }

      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if(!is_null($fastTask)) {
        foreach ($fastTask->getPripremaZadataks() as $fast) {
          if (!is_null($fast->getCar())) {
            if ($fast->getCar() == $car->getId()) {
              if (!is_null($fast->getVreme())) {
                $vreme = $fastTask->getDatumKreiranja();
              } else {
                $vreme = $fast->getVreme();
              }

              if (!is_null($fast->getDriver())) {
                $vozac = $fast->getDriver();
              } else {
                $vozac = null;
              }
              $lista[] = [
                'datum' => $vreme,
                'driver' => $vozac,
              ];
            }
          }
        }
      }
    }

    usort($lista, function($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['driver'])) {
        return $this->getEntityManager()->getRepository(User::class)->find($list['driver']);
      }
    }

    return null;

  }

  public function findToolsToReserve(User $user): array {
    $company = $this->security->getUser()->getCompany();
    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];

    foreach ($danasnjiTaskovi as $dnsTask) {
      if ($dnsTask['status'] != 2) {
        if (!$dnsTask['task']->getOprema()->isEmpty()) {
          if ($dnsTask['task']->getPriorityUserLog() == $user->getId()) {
            foreach ($dnsTask['task']->getOprema() as $opr) {
              $lista[] = [
                'datum' => $dnsTask['task']->getTime(),
                'user' => $dnsTask['task']->getPriorityUserLog(),
                'tool' => $opr->getId(),
              ];
            }
          }
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if ($dnsTask['status'] != 2) {
          if (!$dnsTask['task']->getOprema()->isEmpty()) {
            if ($dnsTask['task']->getPriorityUserLog() == $user->getId()) {
              foreach ($dnsTask['task']->getOprema() as $opr) {
                $lista[] = [
                  'datum' => $dnsTask['task']->getTime(),
                  'user' => $dnsTask['task']->getPriorityUserLog(),
                  'tool' => $opr->getId(),
                ];
              }
            }
          }
        }
      }

      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();

      if (!is_null($fastTask)) {
        foreach ($fastTask->getPripremaZadataks() as $fast) {
          if (!$fast->getOprema()->isEmpty()) {
            foreach ($fast->getOprema() as $opr) {
              if ($user->getId() == $fast->getPriorityUserLog()) {
                if (!is_null($fast->getVreme())) {
                  $vreme = $fastTask->getDatumKreiranja();
                } else {
                  $vreme = $fast->getVreme();
                }

                $lista[] = [
                  'datum' => $vreme,
                  'user' => $fast->getPriorityUserLog(),
                  'tool' => $opr->getId(),
                ];
              }
            }
          }
        }
      }
    }

    usort($lista, function ($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });


    $noviNiz = [];

    foreach ($lista as $element) {
      $datum = $element['datum']->getTimestamp();
      if (!isset($noviNiz[$datum])) {
        $noviNiz[$datum] = [];
      }
      $noviNiz[$datum][] = [
        "user" => $element['user'],
        "tool" => $element['tool'],
      ];
    }

    $listaOpreme = [];
    if (!empty($noviNiz)) {
      foreach (reset($noviNiz) as $list) {
        $tool = $this->getEntityManager()->getRepository(Tool::class)->find($list['tool']);
        $listaOpreme[] = [
          'tool' => $tool,
          'lastReservation' => $this->getEntityManager()->getRepository(ToolReservation::class)->findOneBy(['tool' => $tool], ['id' => 'desc'])
        ];
      }
    }
    return $listaOpreme;
  }
  public function whereToolShouldGo(Tool $tool): ?User {
    $company = $this->security->getUser()->getCompany();
    $sutra = new DateTimeImmutable('tomorrow');
    $danas = new DateTimeImmutable();

    $startDate = $danas->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $danas->format('Y-m-d 23:59:59'); // Kraj dana

    $danasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($danas);
    $sutrasnjiTaskovi = $this->getEntityManager()->getRepository(Task::class)->getTasksByDate($sutra);

    $lista = [];

    foreach ($danasnjiTaskovi as $dnsTask) {
      if ($dnsTask['status'] != 2) {
        if (!$dnsTask['task']->getOprema()->isEmpty()) {
          foreach ($dnsTask['task']->getOprema() as $opr) {
            if ($opr == $tool) {
              $lista[] = [
                'datum' => $dnsTask['task']->getTime(),
                'user' => $dnsTask['task']->getPriorityUserLog(),
                'tool' => $tool->getId(),
              ];
            }
          }
        }
      }
    }

    if (empty($lista)) {
      foreach ($sutrasnjiTaskovi as $dnsTask) {
        if ($dnsTask['status'] != 2) {
          if (!$dnsTask['task']->getOprema()->isEmpty()) {
            foreach ($dnsTask['task']->getOprema() as $opr) {
              if ($opr == $tool) {
                $lista[] = [
                  'datum' => $dnsTask['task']->getTime(),
                  'user' => $dnsTask['task']->getPriorityUserLog(),
                  'tool' => $tool->getId(),
                ];
              }
            }
          }
        }
      }

      $qb = $this->createQueryBuilder('f');
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->eq('f.status', ':status2'),
          $qb->expr()->eq('f.status', ':status3')
        ))
        ->andWhere('f.company = :company')
        ->setParameter(':company', $company)
        ->setParameter('status2', FastTaskData::SAVED)
        ->setParameter('status3', FastTaskData::EDIT)
        ->setMaxResults(1); // Postavljamo da vrati samo jedan rezultat

      $query = $qb->getQuery();
      $fastTask = $query->getOneOrNullResult();


      if (!is_null($fastTask)) {
        foreach ($fastTask->getPripremaZadataks() as $fast) {
          if (!$fast->getOprema()->isEmpty()) {
            if ($fast->getOprema()->contains($tool)) {
              if (!is_null($fast->getVreme())) {
                $vreme = $fastTask->getDatumKreiranja();
              } else {
                $vreme = $fast->getVreme();
              }

              $lista[] = [
                'datum' => $vreme,
                'user' => $fast->getPriorityUserLog(),
                'tool' => $tool->getId(),
              ];
            }
          }
        }
      }
    }
    usort($lista, function ($a, $b) {
      return $a['datum'] <=> $b['datum'];
    });

    foreach ($lista as $list) {
      if (!is_null($list['user'])) {
        return $this->getEntityManager()->getRepository(User::class)->find($list['user']);
      }
    }

    return null;

  }

//    /**
//     * @return Plan[] Returns an array of Plan objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Plan
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
