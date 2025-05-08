<?php

namespace App\Repository;

use App\Entity\Elaborat;
use App\Entity\ElaboratInput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ElaboratInput>
 *
 * @method ElaboratInput|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElaboratInput|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElaboratInput[]    findAll()
 * @method ElaboratInput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElaboratInputRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ElaboratInput::class);
  }

  public function save(ElaboratInput $entity): void {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();

  }

  public function remove(ElaboratInput $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();

  }

//    /**
//     * @return ElaboratInput[] Returns an array of ElaboratInput objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ElaboratInput
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}