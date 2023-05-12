<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskLog>
 *
 * @method TaskLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskLog[]    findAll()
 * @method TaskLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskLogRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, TaskLog::class);
  }

  public function findOneByUserPosition(Task $task, int $userPosition): ?TaskLog {

    $log = $this->createQueryBuilder('tl')
      ->innerJoin(User::class, 'u', Join::WITH, 'u = tl.user')
      ->andWhere('tl.task = :taskId')
      ->andWhere('u.pozicija = :position')
      ->setParameter(':taskId', $task->getId())
      ->setParameter(':position', $userPosition)
      ->setMaxResults(1)
      ->getQuery()
      ->getResult();

    if (!empty($log)) {
      return $log[0];
    }
    return null;

  }

  public function findLogs(Task $task): array {
    $logs = [];

    $taskLogs = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $task]);

    foreach ($taskLogs as $log) {
      $count = $this->getEntityManager()->getRepository(StopwatchTime::class)->countStopwatches($log);
      $lastEdit = $this->getEntityManager()->getRepository(StopwatchTime::class)->lastEdit($log);
      $logs[] = ['log' => $log, 'count' => $count, 'lastEdit' => $lastEdit];
    }

    return $logs;

  }

  public function save(TaskLog $entity, bool $flush = false): void {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(TaskLog $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

//    /**
//     * @return TaskLog[] Returns an array of TaskLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TaskLog
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
