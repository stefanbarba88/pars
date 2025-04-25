<?php

namespace App\Repository;

use App\Entity\Element;
use App\Entity\Sastavnica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

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
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security) {
        parent::__construct($registry, Sastavnica::class);
        $this->security = $security;
    }

    public function save(Sastavnica $sastavnica): Sastavnica {
        if (is_null($sastavnica->getId())) {
            $this->getEntityManager()->persist($sastavnica);
        }

        $this->getEntityManager()->flush();
        return $sastavnica;
    }

    public function remove(Sastavnica $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function getSastavnicaPaginator($company, $filter): QueryBuilder {

        $qb = $this->createQueryBuilder('u');


        $qb->where('u.company = :company')
            ->setParameter(':company', $company);

        if (!empty($filter['key'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.productKey', ':key'),
            ))
                ->setParameter('key', '%' . $filter['key'] . '%');
        }
        if (!empty($filter['title'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.title', ':title'),
            ))
                ->setParameter('title', '%' . $filter['title'] . '%');
        }

        $qb
            ->orderBy('u.isSuspended', 'ASC')
            ->addOrderBy('u.title', 'ASC')
            ->getQuery();


        return $qb;

    }

    public function findForForm(int $id = 0): Sastavnica {
        if (empty($id)) {
            $label =  new Sastavnica();
            return $label;
        }
        return $this->getEntityManager()->getRepository(Sastavnica::class)->find($id);
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
