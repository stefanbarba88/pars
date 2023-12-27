<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Company::class);
    $this->security = $security;
  }

  public function save(Company $company): Company {

    if (is_null($company->getId())) {
      $company->setCreatedBy($this->security->getUser());
      $this->getEntityManager()->persist($company);
    } else {
      $company->setEditBy($this->security->getUser());
    }

    $this->getEntityManager()->flush();

    return $company;
  }

  public function remove(Company $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Company {
    if (empty($id)) {
      return new Company();
    }
    return $this->getEntityManager()->getRepository(Company::class)->find($id);
  }

  public function countCompaniesActive(): int {
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.isSuspended = :isSuspended')
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function getAllCompaniesPaginator() {
    return $this->createQueryBuilder('u')
      ->orderBy('u.isSuspended', 'ASC')
      ->getQuery();

  }

//    /**
//     * @return Company[] Returns an array of Company objects
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

//    public function findOneBySomeField($value): ?Company
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
