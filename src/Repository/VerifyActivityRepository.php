<?php

namespace App\Repository;

use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Company;
use App\Entity\StopwatchTime;
use App\Entity\User;
use App\Entity\VerifyActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VerifyActivity>
 *
 * @method VerifyActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerifyActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerifyActivity[]    findAll()
 * @method VerifyActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerifyActivityRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, VerifyActivity::class);
  }

  public function save(VerifyActivity $verifyActivity): VerifyActivity {
    if (is_null($verifyActivity->getId())) {
      $this->getEntityManager()->persist($verifyActivity);
    }

    $this->getEntityManager()->flush();
    return $verifyActivity;
  }

  public function getAllActivitiesToVerify(User $user): Query {

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->createQueryBuilder('p')
        ->andWhere('p.company = :company')
        ->andWhere('p.zaduzeni = :user')
        ->setParameter(':company', $user->getCompany())
        ->setParameter(':user', $user)
        ->orderBy('p.status', 'ASC')
        ->addOrderBy('p.id', 'DESC')
        ->getQuery();
    }

    return $this->createQueryBuilder('p')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $user->getCompany())
      ->orderBy('p.status', 'ASC')
      ->addOrderBy('p.id', 'DESC')
      ->getQuery();

  }

  public function getStatusByUser(User $user) : bool {

    $activity = $this->findOneBy(['zaduzeni' => $user, 'status' => TaskStatusData::NIJE_ZAPOCETO]);
    if (empty($activity)) {
      return true;
    }
    return false;
  }


  public function remove(VerifyActivity $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();

  }

//    /**
//     * @return VerifyActivity[] Returns an array of VerifyActivity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VerifyActivity
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
