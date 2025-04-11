<?php

namespace App\Repository;

use App\Entity\Deo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deo>
 *
 * @method Deo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deo[]    findAll()
 * @method Deo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deo::class);
    }

//    /**
//     * @return Deo[] Returns an array of Deo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Deo
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
