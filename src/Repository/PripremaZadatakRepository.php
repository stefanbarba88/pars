<?php

namespace App\Repository;

use App\Entity\PripremaZadatak;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PripremaZadatak>
 *
 * @method PripremaZadatak|null find($id, $lockMode = null, $lockVersion = null)
 * @method PripremaZadatak|null findOneBy(array $criteria, array $orderBy = null)
 * @method PripremaZadatak[]    findAll()
 * @method PripremaZadatak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PripremaZadatakRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, PripremaZadatak::class);
  }

  public function save(PripremaZadatak $entity): void {

    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();

  }

  public function remove(PripremaZadatak $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

//    /**
//     * @return PripremaZadatak[] Returns an array of PripremaZadatak objects
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

//    public function findOneBySomeField($value): ?PripremaZadatak
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
