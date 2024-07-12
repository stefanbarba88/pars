<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\ToolType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<ToolType>
 *
 * @method ToolType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolType[]    findAll()
 * @method ToolType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolTypeRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, ToolType::class);
    $this->security = $security;
  }

  public function save(ToolType $tool): ToolType {

    if (is_null($tool->getId())) {
      $this->getEntityManager()->persist($tool);
    } else {
      $tool->setEditBy($this->security->getUser());
    }

    $this->getEntityManager()->flush();

    return $tool;
  }

  public function remove(ToolType $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getToolsTypePaginator($filter) {
    $company = $this->security->getUser()->getCompany();


    $qb =  $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company);

    if (!empty($filter['naziv'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.title', ':naziv'),
      ))
        ->setParameter('naziv', '%' . $filter['naziv'] . '%');
    }
//      if (!empty($filter['tip'])) {
//        $qb->andWhere('c.type = :tip');
//        $qb->setParameter('tip', $filter['tip']);
//      }

    $qb->orderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->getQuery();

    return $qb;
  }

  public function findForForm(int $id = 0): ToolType {
    if (empty($id)) {
      $tool = new ToolType();
      $tool->setCompany($this->security->getUser()->getCompany());
      return $tool;
    }
    return $this->getEntityManager()->getRepository(ToolType::class)->find($id);
  }
//    /**
//     * @return ToolType[] Returns an array of ToolType objects
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

//    public function findOneBySomeField($value): ?ToolType
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
