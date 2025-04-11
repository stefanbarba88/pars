<?php

namespace App\Repository;

use App\Entity\Nalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Nalog>
 *
 * @method Nalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nalog[]    findAll()
 * @method Nalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NalogRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Nalog::class);
    $this->security = $security;
  }

  public function save(Nalog $nalog): Nalog {

    if (is_null($nalog->getId())) {
      $this->getEntityManager()->persist($nalog);
    }

    $this->getEntityManager()->flush();
    return $nalog;

  }

  public function remove(Nalog $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getNalogsPaginator(array $filter): Query {
    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->setParameter('company', $company);


    if (!empty($filter['nalogKey'])) {
      $qb->andWhere(
        $qb->expr()->like('c.nalogKey', ':nalogKey')
      )->setParameter('nalogKey', '%' . $filter['nalogKey'] . '%');
    }

    $qb->orderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.status', 'ASC')
      ->addOrderBy('c.created', 'DESC');

    return $qb->getQuery();
  }


  public function findForForm(int $id = 0): Nalog {
    if (empty($id)) {
      $nalog =  new Nalog();
      $nalog->setCompany($this->security->getUser()->getCompany());
      $nalog->setCreatedBy($this->security->getUser());
      return $nalog;
    }
    $nalog = $this->getEntityManager()->getRepository(Nalog::class)->find($id);

    return $nalog;
  }

//    /**
//     * @return Nalog[] Returns an array of Nalog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Nalog
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
