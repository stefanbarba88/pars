<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\ToolHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolHistory>
 *
 * @method ToolHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolHistory[]    findAll()
 * @method ToolHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ToolHistory::class);
  }

  public function save(ToolHistory $tool): ToolHistory {
    if (is_null($tool->getId())) {
      $this->getEntityManager()->persist($tool);
    }

    $this->getEntityManager()->flush();
    return $tool;
  }


  public function remove(ToolHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getToolsHistoryPaginator(Tool $tool) {

    return $this->createQueryBuilder('c')
      ->where('c.tool = :tool')
      ->setParameter('tool', $tool)
      ->orderBy('c.id', 'DESC')
      ->getQuery();


  }

//    /**
//     * @return CarHistory[] Returns an array of CarHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CarHistory
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
