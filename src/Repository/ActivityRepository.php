<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Activity>
 *
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Activity::class);
    $this->security = $security;
  }

  public function save(Activity $activity): Activity {
    if (is_null($activity->getId())) {
      $this->getEntityManager()->persist($activity);
    }

    $this->getEntityManager()->flush();
    return $activity;
  }

  public function remove(Activity $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Activity {
    if (empty($id)) {
      $act =  new Activity();
      $act->setCompany($this->security->getUser()->getCompany());
      return $act;
    }
    return $this->getEntityManager()->getRepository(Activity::class)->find($id);
  }

  public function getActivitiesPaginator($filter) {
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->orWhere('c.company IS NULL')
      ->setParameter('company', $company);

    if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }
     $qb->orderBy('c.isSuspended', 'ASC')
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();

    return $qb;

  }

//    /**
//     * @return Activity[] Returns an array of Activity objects
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

//    public function findOneBySomeField($value): ?Activity
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
