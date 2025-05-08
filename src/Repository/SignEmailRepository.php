<?php

namespace App\Repository;

use App\Entity\SignEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SignEmail>
 *
 * @method SignEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method SignEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method SignEmail[]    findAll()
 * @method SignEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SignEmailRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, SignEmail::class);
  }

  public function save(SignEmail $entity): void {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();
  }

  public function remove(SignEmail $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();

  }

//    /**
//     * @return SignEmail[] Returns an array of SignEmail objects
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

//    public function findOneBySomeField($value): ?SignEmail
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
