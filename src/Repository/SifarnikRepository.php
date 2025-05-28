<?php

namespace App\Repository;

use App\Entity\Sifarnik;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Sifarnik>
 *
 * @method Sifarnik|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sifarnik|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sifarnik[]    findAll()
 * @method Sifarnik[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SifarnikRepository extends ServiceEntityRepository {
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security) {
        parent::__construct($registry, Sifarnik::class);
        $this->security = $security;
    }

    public function save(Sifarnik $Sifarnik): Sifarnik {

        if (is_null($Sifarnik->getId())) {
            $this->getEntityManager()->persist($Sifarnik);
        }

        $this->getEntityManager()->flush();
        return $Sifarnik;
    }

    public function remove(Sifarnik $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findForForm(int $id = 0): Sifarnik {
        if (empty($id)) {
            return new Sifarnik();
        }
        return $this->getEntityManager()->getRepository(Sifarnik::class)->find($id);
    }

    public function getAllCodesPaginator($filter): \Doctrine\ORM\QueryBuilder {


        $qb =  $this->createQueryBuilder('c');

        if (!empty($filter['title'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.title', ':title'),
            ))
                ->setParameter('title', '%' . $filter['title'] . '%');
        }
        if (!empty($filter['short'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.titleShort', ':short'),
            ))
                ->setParameter('short', '%' . $filter['short'] . '%');
        }


        $qb->orderBy('c.title', 'ASC')
            ->getQuery();

        return $qb;

    }

    public function searchByTerm(string $term) {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :term OR p.titleShort LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }


//    /**
//     * @return Sifarnik[] Returns an array of Sifarnik objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sifarnik
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
