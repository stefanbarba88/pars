<?php

namespace App\Repository;

use App\Entity\CarHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarHistory>
 *
 * @method CarHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarHistory[]    findAll()
 * @method CarHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, CarHistory::class);
  }

  public function save(CarHistory $car): CarHistory {
    if (is_null($car->getId())) {
      $this->getEntityManager()->persist($car);
    }

    $this->getEntityManager()->flush();
    return $car;
  }


  public function remove(CarHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

//    /**
//     * @return CarHistory[] Returns an array of CarHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CarHistory
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
