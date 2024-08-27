<?php

namespace App\Repository;

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
//class ManagerChecklistRepository extends ServiceEntityRepository {
//  private Security $security;
//  public function __construct(ManagerRegistry $registry, Security $security) {
//    parent::__construct($registry, ManagerChecklist::class);
//    $this->security = $security;
//  }
//
//  public function save(ManagerChecklist $checklist): ManagerChecklist {
//
//    if (is_null($checklist->getId())) {
//      $this->getEntityManager()->persist($checklist);
//    }
//
//    $this->getEntityManager()->flush();
//    return $checklist;
//  }
//
//
////  public function getChecklistPaginator(User $loggedUser, $status) {
////    $company = $this->security->getUser()->getCompany();
////    return match ($loggedUser->getUserType()) {
////      UserRolesData::ROLE_SUPER_ADMIN => $this->createQueryBuilder('c')
////        ->where('c.company = :company')
////        ->setParameter('company', $company)
////        ->andWhere('c.status = :status')
////        ->setParameter('status', $status)
////        ->orderBy('c.status', 'ASC')
////        ->addOrderBy('c.priority', 'ASC')
////        ->addOrderBy('c.created', 'DESC')
////        ->getQuery(),
////
////      default => $this->createQueryBuilder('c')
////        ->andWhere('c.createdBy = :user')
////        ->setParameter(':user', $loggedUser)
////        ->andWhere('c.status = :status')
////        ->setParameter('status', $status)
////        ->orderBy('c.status', 'ASC')
////        ->addOrderBy('c.priority', 'ASC')
////        ->addOrderBy('c.created', 'DESC')
////        ->getQuery(),
////
////    };
////
////  }
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
//    public function getChecklistToDoPaginator(User $loggedUser): Query {
//
//    return  $this->createQueryBuilder('c')
//      ->andWhere('c.user = :user')
//      ->setParameter(':user', $loggedUser)
//      ->orderBy('c.status', 'ASC')
//      ->addOrderBy('c.datumKreiranja', 'ASC')
//      ->addOrderBy('c.priority', 'ASC')
//      ->getQuery();
//
//  }
//
//
//  public function finish(ManagerChecklist $checklist): ManagerChecklist {
//
//    $checklist->setStatus(1);
//    $checklist->setFinish(new DateTimeImmutable());
//
//    $this->save($checklist);
//    return $checklist;
//  }
//
//  public function start(ManagerChecklist $checklist): ManagerChecklist {
//
//    $checklist->setStatus(0);
//    $checklist->setFinish(null);
//
//    $this->save($checklist);
//    return $checklist;
//  }
//
//  public function delete(ManagerChecklist $checklist): ManagerChecklist {
//
//    $checklist->setStatus(2);
//
//
//    $this->save($checklist);
//    return $checklist;
//  }
//
//  public function remove(ManagerChecklist $entity, bool $flush = false): void {
//    $this->getEntityManager()->remove($entity);
//
//    if ($flush) {
//      $this->getEntityManager()->flush();
//    }
//  }
//
//  public function findForForm(int $id = 0): ManagerChecklist {
//    if (empty($id)) {
//      $label =  new ManagerChecklist();
//      $label->setCompany($this->security->getUser()->getCompany());
//      return $label;
//    }
//    return $this->getEntityManager()->getRepository(ManagerChecklist::class)->find($id);
//  }
//
//
//  public function findForFormUser(User $user = null): ManagerChecklist {
//
//    $task = new ManagerChecklist();
//    $task->setUser($user);
//    $task->setCompany($user->getCompany());
//    return $task;
//
//  }
//
//
////    /**
////     * @return ManagerChecklist[] Returns an array of ManagerChecklist objects
////     */
////    public function findByExampleField($value): array
////    {
////        return $this->createQueryBuilder('m')
////            ->andWhere('m.exampleField = :val')
////            ->setParameter('val', $value)
////            ->orderBy('m.id', 'ASC')
////            ->setMaxResults(10)
////            ->getQuery()
////            ->getResult()
////        ;
////    }
//
////    public function findOneBySomeField($value): ?ManagerChecklist
////    {
////        return $this->createQueryBuilder('m')
////            ->andWhere('m.exampleField = :val')
////            ->setParameter('val', $value)
////            ->getQuery()
////            ->getOneOrNullResult()
////        ;
////    }
//}

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


  public function getChecklistPaginator($status): Query {

    $company = $this->security->getUser()->getCompany();

    if ($status == InternTaskStatusData::ZAVRSENO) {
      return $this->createQueryBuilder('c')
        ->where('c.company = :company')
        ->setParameter('company', $company)
        ->andWhere('c.status = :status')
        ->setParameter('status', $status)
        ->orderBy('c.datumKreiranja', 'DESC')
        ->addOrderBy('c.priority', 'ASC')
        ->getQuery();
    } else {
      return $this->createQueryBuilder('c')
        ->where('c.company = :company')
        ->setParameter('company', $company)
        ->andWhere('c.status = :status')
        ->setParameter('status', $status)
        ->orderBy('c.datumKreiranja', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->getQuery();
    }

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

  public function getChecklistForCommand() {

    return  $this->createQueryBuilder('c')
      ->andWhere('c.status <> :status and c.status <> :status1')
      ->setParameter(':status', InternTaskStatusData::ZAVRSENO)
      ->setParameter(':status1', InternTaskStatusData::KONVERTOVANO)
      ->getQuery()
      ->getResult();

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

  public function getInternTasksByDateUser(DateTimeImmutable $date, User $user): array  {
    $company = $this->security->getUser()->getCompany();
    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
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
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
      ->andWhere('t.status <> 4')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->orderBy('t.priority', 'ASC');

    $query = $qb->getQuery();
    return $query->getResult();
  }

  public function getTasksByProjectPaginator($filterBy, User $user, Project $project){
    $company = $project->getCompany();
    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme
//    $endDate = $today->sub(new DateInterval('P1D')); // Trenutni datum
    $endDate = $today; // Trenutni datum


    $qb = $this->createQueryBuilder('c');
    if (!empty($filterBy['period'])) {

      $dates = explode(' - ', $filterBy['period']);

      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb->where($qb->expr()->between('c.datumKreiranja', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

    } else {
      $qb->where('c.datumKreiranja < :endDate');
      $qb->setParameter('endDate', $endDate);
    }

    $qb->andWhere('c.company = :company');
    $qb->setParameter(':company', $company);
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

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      $qb->andWhere('c.user = :zaposleni');
      $qb->setParameter('zaposleni', $user);
//      $qb->join('c.assignedUsers', 'u'); // Zamijenite 'u' imenom koje odgovara vašoj entitetu za korisnike (user entity).
//      $qb->andWhere($qb->expr()->in('u.id', ':zaposleni'));
//      $qb->setParameter('zaposleni', $user->getId());
    }


    $qb->orderBy('c.status', 'ASC')
      ->addOrderBy('c.datumKreiranja', 'ASC')
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
