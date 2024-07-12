<?php

namespace App\Repository;

use App\Entity\Addon;
use App\Entity\Company;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Addon>
 *
 * @method Addon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Addon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Addon[]    findAll()
 * @method Addon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddonRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Addon::class);
    $this->security = $security;
  }

  public function save(Addon $addon): Addon {
    if (is_null($addon->getId())) {
      $this->getEntityManager()->persist($addon);
    }

    $this->getEntityManager()->flush();
    return $addon;
  }

  public function remove(Addon $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $user, int $id = 0): Addon {

    if (empty($id)) {
      $zaposleni = $this->getEntityManager()->getRepository(User::class)->find($user);
      $addon = new Addon();
      $addon->setCompany($zaposleni->getCompany());
      $addon->setUser($zaposleni);
      $addon->setCreatedBy($this->security->getUser());
      return $addon;
    }
    return $this->getEntityManager()->getRepository(Addon::class)->find($id);
  }

  public function getAddonsByUser(User $user, $filterBy){

    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme
//    $endDate = $today->sub(new DateInterval('P1D')); // Trenutni datum
    $endDate = $today; // Trenutni datum


    $qb = $this->createQueryBuilder('t');

    if (!empty($filterBy['period'])) {

      $dates = explode(' - ', $filterBy['period']);

      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb->where($qb->expr()->between('t.datum', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

    }

    $qb->andWhere('t.user = :user');
    $qb->setParameter(':user', $user);

    if (!empty($filterBy['addon'])) {
      $qb->andWhere('t.type = :addon');
      $qb->setParameter('addon', $filterBy['addon']);
    }


    $qb->orderBy('t.isSuspended', 'ASC')
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

  public function getAddonsByCompany(Company $company, $filterBy){

    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme
//    $endDate = $today->sub(new DateInterval('P1D')); // Trenutni datum
    $endDate = $today; // Trenutni datum


    $qb = $this->createQueryBuilder('t');

    if (!empty($filterBy['period'])) {

      $dates = explode(' - ', $filterBy['period']);

      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb->where($qb->expr()->between('t.datum', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

    }

    $qb->andWhere('t.company = :company');
    $qb->setParameter(':company', $company);

    if (!empty($filterBy['addon'])) {
      $qb->andWhere('t.type = :addon');
      $qb->setParameter('addon', $filterBy['addon']);
    }
    if (!empty($filterBy['user'])) {
      $user = $this->getEntityManager()->getRepository(User::class)->find($filterBy['user']);
      $qb->andWhere('t.user = :user');
      $qb->setParameter('user', $user);
    }


    $qb->orderBy('t.isSuspended', 'ASC')
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

//    /**
//     * @return Addon[] Returns an array of Addon objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Addon
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
