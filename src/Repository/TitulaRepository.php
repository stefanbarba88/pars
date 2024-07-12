<?php

namespace App\Repository;

use App\Entity\Titula;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Titula>
 *
 * @method Titula|null find($id, $lockMode = null, $lockVersion = null)
 * @method Titula|null findOneBy(array $criteria, array $orderBy = null)
 * @method Titula[]    findAll()
 * @method Titula[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitulaRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Titula::class);
    $this->security = $security;
  }

  public function save(Titula $titula): Titula {
    if (is_null($titula->getId())) {
      $this->getEntityManager()->persist($titula);
    }

    $this->getEntityManager()->flush();
    return $titula;
  }

  public function remove(Titula $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getZvanjaPaginator($filter): Query {

    $qb = $this->createQueryBuilder('c');

    if (!empty($filter['naziv'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.title', ':naziv'),
      ))
        ->setParameter('naziv', '%' . $filter['naziv'] . '%');
    }
    if (!empty($filter['id'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.id', ':id'),
      ))
        ->setParameter('id', '%' . $filter['id'] . '%');
    }

    return $qb
      ->orderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();
  }

  public function findForForm(int $id = 0): Titula {
    if (empty($id)) {
      return new Titula();
    }
    return $this->getEntityManager()->getRepository(Titula::class)->find($id);
  }
//    /**
//     * @return Titula[] Returns an array of Titula objects
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

//    public function findOneBySomeField($value): ?Titula
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
