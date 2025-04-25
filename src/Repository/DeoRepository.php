<?php

namespace App\Repository;

use App\Entity\Deo;
use App\Entity\Sastavnica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

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
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security) {
        parent::__construct($registry, Deo::class);
        $this->security = $security;
    }

    public function save(Deo $sastavnica): Deo {
        if (is_null($sastavnica->getId())) {
            $this->getEntityManager()->persist($sastavnica);
        }

        $this->getEntityManager()->flush();
        return $sastavnica;
    }

    public function remove(Deo $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
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
