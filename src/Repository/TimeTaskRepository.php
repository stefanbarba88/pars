<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\TimeTask;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<TimeTask>
 *
 * @method TimeTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeTask[]    findAll()
 * @method TimeTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeTaskRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, TimeTask::class);
    $this->security = $security;
  }


  public function save(TimeTask $task): TimeTask {
    if (is_null($task->getId())) {
      $this->getEntityManager()->persist($task);
    }

    $this->getEntityManager()->flush();
    return $task;
  }

  public function remove(TimeTask $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): TimeTask {
    if (empty($id)) {
      $task = new TimeTask();
      $task->setCompany($this->security->getUser()->getCompany());
      $task->setUser($this->security->getUser());
      return $task;
    }
    return $this->getEntityManager()->getRepository(TimeTask::class)->find($id);

  }

//    /**
//     * @return TimeTask[] Returns an array of TimeTask objects
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

//    public function findOneBySomeField($value): ?TimeTask
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
