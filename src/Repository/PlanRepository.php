<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Entity\Activity;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Plan;
use App\Entity\PripremaZadatak;
use App\Entity\Project;
use App\Entity\Tool;
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
