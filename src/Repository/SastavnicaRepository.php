<?php

namespace App\Repository;

use App\Entity\Sastavnica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sastavnica>
 *
 * @method Sastavnica|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sastavnica|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sastavnica[]    findAll()
 * @method Sastavnica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SastavnicaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sastavnica::class);
    }

//    /**
//     * @return Sastavnica[] Returns an array of Sastavnica objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sastavnica
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
