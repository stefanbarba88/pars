<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 *
 * @method City|null find($id, $lockMode = null, $lockVersion = null)
 * @method City|null findOneBy(array $criteria, array $orderBy = null)
 * @method City[]    findAll()
 * @method City[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CityRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, City::class);
  }

  public function save(City $city): City {
    if (is_null($city->getId())) {
      $this->getEntityManager()->persist($city);
    }

    $this->getEntityManager()->flush();
    return $city;
  }

  public function remove(City $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): City {
    if (empty($id)) {
      return new City();
    }
    return $this->getEntityManager()->getRepository(City::class)->find($id);
  }

  public function getRegionsSerbia(): array {

    $regions = [];
    $queryBuilder = $this->getEntityManager()->getRepository(City::class)
      ->createQueryBuilder('c');
    $query = $queryBuilder
      ->select("c")
      ->groupBy("c.region")
      ->getQuery();

    foreach ($query->getResult() as $reg) {
      $regions[] = $reg->getRegion();
    }

    return $regions;
  }

  public function getMunicipalitiesSerbia(): array {

    $municipalities = [];
    $queryBuilder = $this->getEntityManager()->getRepository(City::class)
      ->createQueryBuilder('c');
    $query = $queryBuilder
      ->select("c")
      ->groupBy("c.municipality")
      ->getQuery();

    foreach ($query->getResult() as $reg) {
      $municipalities[] = $reg->getMunicipality();
    }

    return $municipalities;
  }

  public function getCitiesPaginator() {

    return $this->createQueryBuilder('c')
      ->orderBy('c.isSuspended', 'ASC')
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();


  }


//    /**
//     * @return City[] Returns an array of City objects
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

//    public function findOneBySomeField($value): ?City
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
