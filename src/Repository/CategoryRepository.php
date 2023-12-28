<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Category::class);
    $this->security = $security;
  }

  public function save(Category $category): Category {
    if (is_null($category->getId())) {
      $this->getEntityManager()->persist($category);
    }

    $this->getEntityManager()->flush();
    return $category;
  }

  public function remove(Category $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Category {
    if (empty($id)) {
      $cat =  new Category();
      $cat->setCompany($this->security->getUser()->getCompany());
      return $cat;
    }
    return $this->getEntityManager()->getRepository(Category::class)->find($id);
  }

  public function getCategoriesPaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->orWhere('c.company IS NULL')
      ->setParameter('company', $company)
      ->orderBy('c.isSuspended', 'ASC')
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();


  }

  public function getCategoriesProject() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->orWhere('c.company IS NULL')
      ->andWhere('c.isSuspended <> 1')
      ->andWhere('c.isTaskCategory = 1')
      ->setParameter('company', $company)
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery()
      ->getResult();


  }

  public function getCategoriesTask() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->orWhere('c.company is NULL')
      ->andWhere('c.isSuspended <> 1')
      ->andWhere('c.isTaskCategory = 1')
      ->setParameter('company', $company)
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery()
      ->getResult();


  }

//    /**
//     * @return Category[] Returns an array of Category objects
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

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
