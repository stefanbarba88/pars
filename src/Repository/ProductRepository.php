<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Deo;
use App\Entity\Product;
use App\Entity\Production;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Product::class);
    $this->security = $security;
  }


  public function save(Product $product): Product {

    if (is_null($product->getId())) {
      $this->getEntityManager()->persist($product);
    }

    $this->getEntityManager()->flush();
    return $product;

  }

  public function remove(Product $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function searchByTerm(string $term) {
    return $this->createQueryBuilder('p')
//      ->select('p.id, p.title, p.productKey') // Include the root entity 'u'
      ->where('p.title LIKE :term')
      ->orWhere('p.productKey LIKE :term')
      ->andWhere('p.isSuspended = 0')
      ->setParameter('term', '%' . $term . '%')
      ->getQuery()
      ->getResult();
  }

  public function getProductsPaginator(Company $company, array $filter): Query {

    $qb = $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->setParameter('company', $company);

    if (!empty($filter['title'])) {
      $qb->andWhere(
        $qb->expr()->like('c.title', ':title')
      )->setParameter('title', '%' . $filter['title'] . '%');
    }

    if (!empty($filter['productKey'])) {
      $qb->andWhere(
        $qb->expr()->like('c.productKey', ':productKey')
      )->setParameter('productKey', '%' . $filter['productKey'] . '%');
    }

    $qb->orderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC');

    return $qb->getQuery();
  }


    public function getChartData(?Company $company, $start, $stop): array {

        $productions = $this->getEntityManager()->getRepository(Production::class)->getProductionByDate($company, $start, $stop);



        $productQuantities = [];

        if (!empty($productions)) {
            foreach ($productions as $production) {
                foreach ($production->getDeo()->toArray() as $item) {

                        $productId = $item->getProduct()->getId();
                        $productName = $item->getProduct()->getTitle();
                        $productKey = $item->getProduct()->getProductKey();
                        $quantity = $item->getKolicina();

                        if (!isset($productQuantities[$productId])) {
                            $productQuantities[$productId] = [
                                'title' => $productName,
                                'productKey' => $productKey,
                                'quantity' => 0,
                            ];
                        }

                        $productQuantities[$productId]['quantity'] += $quantity;


                };
            }
        }

        usort($productQuantities, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($productQuantities, 0, 5);


    }

  public function findForForm(int $id = 0): Product {
    if (empty($id)) {
      $product =  new Product();
      $product->setCompany($this->security->getUser()->getCompany());
      return $product;
    }
    $product = $this->getEntityManager()->getRepository(Product::class)->find($id);

    return $product;
  }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
