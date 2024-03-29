<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\TaskHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskHistory>
 *
 * @method TaskHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskHistory[]    findAll()
 * @method TaskHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, TaskHistory::class);
  }

  public function save(TaskHistory $task): TaskHistory {
    if (is_null($task->getId())) {
      $this->getEntityManager()->persist($task);
    }

    $this->getEntityManager()->flush();
    return $task;
  }

  public function remove(TaskHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }



//    /**
//     * @return TaskHistory[] Returns an array of TaskHistory objects
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

//    public function findOneBySomeField($value): ?TaskHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
