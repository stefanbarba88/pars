<?php

namespace App\Repository;

use App\Classes\CompanyInfo;
use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Category;
use App\Entity\Company;
use App\Entity\ManagerChecklist;
use App\Entity\Project;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<ManagerChecklist>
 *
 * @method ManagerChecklist|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerChecklist|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerChecklist[]    findAll()
 * @method ManagerChecklist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerChecklistRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, ManagerChecklist::class);
    $this->security = $security;
  }

  public function save(ManagerChecklist $checklist): ManagerChecklist {

    if (is_null($checklist->getId())) {
      $this->getEntityManager()->persist($checklist);
    }

    $this->getEntityManager()->flush();
    return $checklist;
  }


//  public function getChecklistPaginator($status): Query {
//
//    $company = $this->security->getUser()->getCompany();
//
//    if ($status == InternTaskStatusData::ZAVRSENO) {
//      return $this->createQueryBuilder('c')
//        ->where('c.company = :company')
//        ->setParameter('company', $company)
//        ->andWhere('c.status = :status')
//        ->setParameter('status', $status)
//        ->orderBy('c.datumKreiranja', 'DESC')
//        ->addOrderBy('c.priority', 'ASC')
//        ->getQuery();
//    } else {
//      return $this->createQueryBuilder('c')
//        ->where('c.company = :company')
//        ->setParameter('company', $company)
//        ->andWhere('c.status = :status')
//        ->setParameter('status', $status)
//        ->orderBy('c.datumKreiranja', 'ASC')
//        ->addOrderBy('c.priority', 'ASC')
//        ->getQuery();
//    }
//
//  }
  public function getChecklistPaginator($status, $user): Query {

    $company = $this->security->getUser()->getCompany();

    if ($user->getUserType() == UserRolesData::ROLE_SUPER_ADMIN || $user->getUserType() == UserRolesData::ROLE_ADMIN || $user->isAdmin()) {
      if ($status == InternTaskStatusData::ZAVRSENO) {
        return $this->createQueryBuilder('c')
          ->where('c.company = :company')
          ->setParameter('company', $company)
          ->andWhere('c.status = :status or c.status = :status1')
          ->setParameter('status', $status)
          ->setParameter('status1', InternTaskStatusData::KONVERTOVANO)
          ->orderBy('c.datumKreiranja', 'DESC')
          ->addOrderBy('c.priority', 'ASC')
          ->addOrderBy('c.deadline', 'ASC')
          ->getQuery();
      } else {
        return $this->createQueryBuilder('c')
          ->where('c.company = :company')
          ->setParameter('company', $company)
          ->andWhere('c.status = :status')
          ->setParameter('status', $status)
          ->orderBy('c.datumKreiranja', 'DESC')
          ->addOrderBy('c.priority', 'ASC')
          ->addOrderBy('c.deadline', 'ASC')
          ->getQuery();
      }
    } else {
      if ($status == InternTaskStatusData::ZAVRSENO) {
        return $this->createQueryBuilder('c')
          ->where('c.company = :company')
          ->setParameter('company', $company)
          ->andWhere('c.status = :status')
          ->setParameter('status', $status)
          ->andWhere('c.createdBy = :user')
          ->setParameter('user', $user)
          ->orderBy('c.datumKreiranja', 'DESC')
          ->addOrderBy('c.priority', 'ASC')
          ->getQuery();
      } else {
        return $this->createQueryBuilder('c')
          ->where('c.company = :company')
          ->setParameter('company', $company)
          ->andWhere('c.status = :status')
          ->setParameter('status', $status)
          ->andWhere('c.createdBy = :user')
          ->setParameter('user', $user)
          ->orderBy('c.datumKreiranja', 'ASC')
          ->addOrderBy('c.priority', 'ASC')
          ->getQuery();
      }
    }



  }


  public function getDaysRemaining(ManagerChecklist $task): array {
    $poruka = '';
    $klasa = '';
    $klasa1 = '';
    $now = new DateTimeImmutable();
    $now->setTime(0,0);

    if (!is_null($task->getDeadline())) {
      $contractEndDate = $task->getDeadline();
      // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
      $days = (int) $now->diff($contractEndDate)->format('%R%a');

      if ($days > 0 && $days < 7) {
        $poruka = 'Rok za završetak zadatka ističe za ' . $days . ' dana.';
        $klasa = 'bg-info bg-opacity-50';
      } elseif ($days == 0) {
        $poruka = 'Rok za završetak zadatka ističe danas.';
        $klasa = 'bg-warning bg-opacity-50';
        $klasa1 = 'bg-warning bg-opacity-10';
      } elseif ($days < 0) {
        $poruka = 'Rok za završetak zadatka je istekao pre ' . abs($days) . ' dana.';
        $klasa = 'bg-danger bg-opacity-50';
        $klasa1 = 'bg-danger bg-opacity-10';
      }
    }

    return [
      'klasa' => $klasa,
      'poruka' => $poruka,
      'klasa1' => $klasa1
    ];
  }

  public function countInternTasks(Company $company): int{

    $qb = $this->createQueryBuilder('c');

    $qb->select($qb->expr()->count('c'))
      ->andWhere('c.status = :status')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', InternTaskStatusData::NIJE_ZAPOCETO);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function getChecklistToDoPaginator(User $loggedUser) {

    return  $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->setParameter(':user', $loggedUser)
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.datumKreiranja', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->getQuery();

  }
  public function getChecklistToDoHomePaginator(User $loggedUser) {

    return  $this->createQueryBuilder('c')
      ->andWhere('c.user = :user')
      ->andWhere('c.status <> :status AND c.status <> :status1')
      ->setParameter(':user', $loggedUser)
      ->setParameter(':status', InternTaskStatusData::KONVERTOVANO)
      ->setParameter(':status1', InternTaskStatusData::ZAVRSENO)
      ->orderBy('c.status', 'ASC')
      ->addOrderBy('c.datumKreiranja', 'ASC')
      ->addOrderBy('c.priority', 'ASC')
      ->getQuery();

  }

  public function getChecklistToDoHomeCompanyPaginator(Company $company) {

    return  $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->andWhere('c.status <> :status AND c.status <> :status1')
      ->setParameter(':company', $company)
      ->setParameter(':status', InternTaskStatusData::KONVERTOVANO)
      ->setParameter(':status1', InternTaskStatusData::ZAVRSENO)
      ->orderBy('c.status', 'DESC')
      ->addOrderBy('c.datumKreiranja', 'ASC')
      ->addOrderBy('c.deadline', 'ASC')
      ->addOrderBy('c.priority', 'ASC')
      ->getQuery();

  }

  public function getInternTasksByDateUser(DateTimeImmutable $date, User $user): array  {
    $company = $this->security->getUser()->getCompany();
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->modify('+ 1 day')->format('Y-m-d 00:00:00'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
      ->andWhere($qb->expr()->between('t.deadline', ':start', ':end'))
      ->andWhere('t.status <> 4')
      ->andWhere('t.company = :company')
      ->andWhere('t.user = :user')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter('user', $user)
      ->orderBy('t.priority', 'ASC');

    $query = $qb->getQuery();
    return $query->getResult();
  }


  public function getChecklist(User $user, DateTimeImmutable $finish): array {

    $startDate = $finish->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $finish->format('Y-m-d 23:59:59');

    return  $this->createQueryBuilder('c')
      ->where('c.user = :user')
      ->andWhere('c.finish BETWEEN :startDate AND :endDate')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter(':user', $user)
      ->getQuery()
      ->getResult();

  }

  public function getInternTasksByDate(DateTimeImmutable $date): array  {
    $company = $this->security->getUser()->getCompany();
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->modify('+ 1 day')->format('Y-m-d 00:00:00'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
      ->andWhere($qb->expr()->between('t.deadline', ':start', ':end'))
      ->andWhere('t.status <> 4')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->orderBy('t.priority', 'ASC');

    $query = $qb->getQuery();
    return $query->getResult();
  }
  public function getInternTasksAdminHome(): Query {
    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where('t.status <> 4')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->orderBy('t.priority', 'ASC');

    return $qb->getQuery();
  }

  public function getTasksByProjectPaginator($filterBy, User $user, Project $project, $admin){
    $company = $project->getCompany();
    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme
//    $endDate = $today->sub(new DateInterval('P1D')); // Trenutni datum
    $endDate = $today; // Trenutni datum


    $qb = $this->createQueryBuilder('c');
    $qb->where('c.company = :company');
    $qb->setParameter(':company', $company);
    if (!empty($filterBy['period'])) {

      $dates = explode(' - ', $filterBy['period']);

      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb->where($qb->expr()->between('c.datumKreiranja', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

    }


    $qb->andWhere('c.project = :projekat');
    $qb->setParameter('projekat', $project);

    if (!empty($filterBy['kategorija'])) {
      $qb->andWhere('c.category = :kategorija');
      $qb->setParameter('kategorija', $filterBy['kategorija']);
    }
    if (!empty($filterBy['zaposleni'])) {
      $qb->andWhere('c.user = :zaposleni');
      $qb->setParameter('zaposleni', $filterBy['zaposleni']);
//      $qb->join('c.assignedUsers', 'u'); // Zamijenite 'u' imenom koje odgovara vašoj entitetu za korisnike (user entity).
//      $qb->andWhere($qb->expr()->in('u.id', ':zaposleni'));
//      $qb->setParameter('zaposleni', $filterBy['zaposleni']);
    }

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE && !$admin) {
      $qb->andWhere('c.user = :zaposleni');
      $qb->setParameter('zaposleni', $user);
//      $qb->join('c.assignedUsers', 'u'); // Zamijenite 'u' imenom koje odgovara vašoj entitetu za korisnike (user entity).
//      $qb->andWhere($qb->expr()->in('u.id', ':zaposleni'));
//      $qb->setParameter('zaposleni', $user->getId());
    }


    $qb->orderBy('c.status', 'ASC')
      ->addOrderBy('c.datumKreiranja', 'DESC')
      ->addOrderBy('c.priority', 'ASC')
      ->getQuery();

//    foreach ($tasks as $task) {
//      $status = $this->taskStatus($task);
//
//      if ($status == TaskStatusData::ZAVRSENO ) {
//        $list[] = [
//          'task' => $task,
//          'status' => $status,
//          'logStatus' => $this->getEntityManager()->getRepository(TaskLog::class)->getLogStatus($task)
//        ];
//      }
//    }
//    usort($list, function ($a, $b) {
//      return $a['status'] <=> $b['status'];
//    });

    return $qb;
  }
  public function getInternTasks($data): array {

    $tasks = [];
     if (!isset($data['checklist'])) {
       return $tasks;
     } else {
       $dates = explode(' - ', $data['period']);

       $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
       $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
       $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
       $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

       $user = $this->getEntityManager()->getRepository(User::class)->find($data['zaposleni']);

       if (isset($data['category'])){
         foreach ($data['category'] as $cat) {
           $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
         }
       }

       $qb = $this->createQueryBuilder('c');
       $qb->where($qb->expr()->between('c.datumKreiranja', ':start', ':end'));
       $qb->setParameter('start', $startDate);
       $qb->setParameter('end', $stopDate);

       $qb->andWhere('c.status = :status');
       $qb->setParameter('status', InternTaskStatusData::ZAVRSENO);

       $qb->andWhere('c.user = :zaposleni');
       $qb->setParameter('zaposleni', $user);

       $qb->addOrderBy('c.datumKreiranja', 'ASC');

       $query = $qb->getQuery();
       $tasks = $query->getResult();

       if (!empty($kategorija)) {
         $katTasks = [];
         foreach ($tasks as $task) {
           if (in_array($task->getCategory(), $kategorija)) {
             $katTasks [] = $task;
           }
         }
         return $katTasks;
       }
       return $tasks;
     }

  }

  public function getInternTasksProject($data, Project $project): array {

    $tasks = [];
    if (!isset($data['checklist'])) {
      return $tasks;
    } else {
      $dates = explode(' - ', $data['period']);

      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      if (isset($data['category'])){
        foreach ($data['category'] as $cat) {
          $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
        }
      }

      $qb = $this->createQueryBuilder('c');
      $qb->where($qb->expr()->between('c.datumKreiranja', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

      $qb->andWhere('c.status = :status');
      $qb->setParameter('status', InternTaskStatusData::ZAVRSENO);

      $qb->andWhere('c.project = :project');
      $qb->setParameter('project', $project);

      $qb->addOrderBy('c.datumKreiranja', 'ASC');

      $query = $qb->getQuery();
      $tasks = $query->getResult();

      if (!empty($kategorija)) {
        $katTasks = [];
        foreach ($tasks as $task) {
          if (in_array($task->getCategory(), $kategorija)) {
            $katTasks [] = $task;
          }
        }
        return $katTasks;
      }
      return $tasks;
    }

  }
  public function countInternTasksProject(DateTimeImmutable $start, DateTimeImmutable $stop, Project $project): array {

      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb = $this->createQueryBuilder('c');
      $qb->where($qb->expr()->between('c.datumKreiranja', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

      $qb->andWhere('c.status <> :status');
      $qb->setParameter('status', InternTaskStatusData::KONVERTOVANO);

      $qb->andWhere('c.project = :project');
      $qb->setParameter('project', $project);

      $qb->addOrderBy('c.datumKreiranja', 'ASC');

      $query = $qb->getQuery();
      $tasks = $query->getResult();

      if (!empty($kategorija)) {
        $katTasks = [];
        foreach ($tasks as $task) {
          if (in_array($task->getCategory(), $kategorija)) {
            $katTasks [] = $task;
          }
        }
        return $katTasks;
      }
      return $tasks;


  }

  public function finish(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setFinish(new DateTimeImmutable());

    $this->save($checklist);
    return $checklist;
  }

  public function start(ManagerChecklist $checklist, User $koris): ManagerChecklist {

    $checklist->setStatus(InternTaskStatusData::ZAPOCETO);
    $checklist->setEditBy($koris);
    $checklist->setFinish(null);

    $this->save($checklist);
    return $checklist;
  }

  public function replay(ManagerChecklist $checklist, User $koris): ManagerChecklist {

    $checklist->setStatus(InternTaskStatusData::NIJE_ZAPOCETO);
    $checklist->setEditBy($koris);
    $checklist->setFinish(null);
    $checklist->setFinishDesc(null);

    $this->save($checklist);
    return $checklist;
  }

  public function delete(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setStatus(2);


    $this->save($checklist);
    return $checklist;
  }

  public function remove(ManagerChecklist $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();
  }

  public function findForForm(int $id = 0): ManagerChecklist {
    if (empty($id)) {
      $label =  new ManagerChecklist();
      $label->setCompany($this->security->getUser()->getCompany());
      return $label;
    }
    return $this->getEntityManager()->getRepository(ManagerChecklist::class)->find($id);
  }


  public function findForFormUser(User $user = null): ManagerChecklist {

    $task = new ManagerChecklist();
    $task->setUser($user);
    $task->setCompany($user->getCompany());
    return $task;

  }

  public function getChecklistUser(User $loggedUser) {

    return  $this->createQueryBuilder('c')
      ->andWhere('c.user = :user')
      ->setParameter(':user', $loggedUser)
      ->andWhere('c.status <> :status and c.status <> :status1')
      ->setParameter(':status', InternTaskStatusData::ZAVRSENO)
      ->setParameter(':status1', InternTaskStatusData::KONVERTOVANO)
      ->orderBy('c.status', 'ASC')
      ->addOrderBy('c.datumKreiranja', 'ASC')
      ->addOrderBy('c.priority', 'ASC')
      ->getQuery()
      ->getResult();

  }
  public function getChecklistCreatedByUserActive(User $loggedUser) {

    return  $this->createQueryBuilder('c')
      ->andWhere('c.createdBy = :user')
      ->setParameter(':user', $loggedUser)
      ->andWhere('c.status <> :status and c.status <> :status1')
      ->setParameter(':status', InternTaskStatusData::ZAVRSENO)
      ->setParameter(':status1', InternTaskStatusData::KONVERTOVANO)
      ->orderBy('c.status', 'ASC')
      ->addOrderBy('c.datumKreiranja', 'ASC')
      ->addOrderBy('c.priority', 'ASC')
      ->getQuery()
      ->getResult();

  }

//    /**
//     * @return ManagerChecklist[] Returns an array of ManagerChecklist objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ManagerChecklist
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
