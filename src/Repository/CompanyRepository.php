<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\User;
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

  public function saveCompany(Company $company): Company {

    $this->getEntityManager()->flush();

    return $company;
  }

  public function register(Company $company): Company {

    $this->getEntityManager()->persist($company);
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

  public function findForFormKadrovska(int $id = 0): Company {
    if (empty($id)) {
      $company = new Company();
      $company->setCreatedBy($this->security->getUser());
      $company->setFirma($this->security->getUser()->getCompany()->getId());
      return $company;
    }
    return $this->getEntityManager()->getRepository(Company::class)->find($id);
  }
  public function getAllCompaniesPaginatorKadrovska($filter, int $firma) {

    $qb = $this->createQueryBuilder('u');

    $qb->where('u.firma = :firma')
      ->andWhere('u.isSuspended = 0')
      ->setParameter(':firma', $firma);

    if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }
    if (!empty($filter['pib'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.pib', ':pib'),
      ))
        ->setParameter('pib', '%' . $filter['pib'] . '%');
    }
    if (!empty($filter['mb'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.mb', ':mb'),
      ))
        ->setParameter('mb', '%' . $filter['mb'] . '%');
    }

    $qb
      ->orderBy('u.title', 'ASC')
      ->getQuery();
    return $qb;
  }
  public function getAllCompaniesPaginatorKadrovskaArchive($filter, int $firma) {

    $qb = $this->createQueryBuilder('u');

    $qb->where('u.firma = :firma')
      ->andWhere('u.isSuspended = 1')
      ->setParameter(':firma', $firma);

    if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }
    if (!empty($filter['pib'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.pib', ':pib'),
      ))
        ->setParameter('pib', '%' . $filter['pib'] . '%');
    }
    if (!empty($filter['mb'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('u.mb', ':mb'),
      ))
        ->setParameter('mb', '%' . $filter['mb'] . '%');
    }

    $qb
      ->orderBy('u.title', 'ASC')
      ->getQuery();
    return $qb;
  }

  public function getCompaniesSearchPaginatorKadrovska($filterBy, User $user){

    $qb = $this->createQueryBuilder('t');

    $qb->where('t.firma = :firma');
    $qb->setParameter(':firma', $user->getCompany()->getId());


    if ($filterBy['status'] == 1) {
      $qb->andWhere('t.isSuspended = :status');
      $qb->setParameter('status', $filterBy['statusStanje']);
    } else {
      $qb->andWhere('t.isSuspended <> :status');
      $qb->setParameter('status', $filterBy['statusStanje']);
    }

    $keywords = explode(" ", $filterBy['tekst']);

    foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('t.title', ':keyword'.$key),
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
    }

    $qb
      ->addOrderBy('t.title', 'ASC')
      ->getQuery();


    return $qb;
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
