<?php

namespace App\Repository;

use App\Entity\TaskEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskEmail>
 *
 * @method TaskEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskEmail[]    findAll()
 * @method TaskEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskEmailRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, TaskEmail::class);
  }

  public function save(TaskEmail $entity): void {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();
  }

  public function remove(TaskEmail $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();

  }

//    /**
//     * @return TaskEmail[] Returns an array of TaskEmail objects
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

//    public function findOneBySomeField($value): ?TaskEmail
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
