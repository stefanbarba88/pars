<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserHistory>
 *
 * @method UserHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHistory[]    findAll()
 * @method UserHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, UserHistory::class);
  }

  public function save(UserHistory $user): UserHistory {
    if (is_null($user->getId())) {
      $this->getEntityManager()->persist($user);
    }

    $this->getEntityManager()->flush();
    return $user;
  }

  public function getAllPaginator(User $user) {

    return $this->createQueryBuilder('u')
      ->andWhere('u.user = :user')
      ->setParameter(':user', $user)
      ->addOrderBy('u.id', 'ASC')
      ->getQuery();
  }

  public function remove(UserHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

//    /**
//     * @return UserHistory[] Returns an array of UserHistory objects
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

//    public function findOneBySomeField($value): ?UserHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
