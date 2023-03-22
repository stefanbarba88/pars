<?php

namespace App\Repository;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 *
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Country::class);
  }

  public function save(Country $entity, bool $flush = false): void {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function remove(Country $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getMunicipalities(): array {

    $cities = $this->getEntityManager()->getRepository(City::class)->findAll();

    $munList = [];
    foreach ($cities as $city) {

      $usersList [] = [
        'id' => $user->getId(),
        'ime' => $user->getIme(),
        'prezime' => $user->getPrezime(),
        'slika' => $this->getEntityManager()->getRepository(Image::class)->findOneBy(['user' => $user]),
        'isSuspended' => $user->getBadgeByStatus(),
        'datumRodjenja' => $user->getDatumRodjenja(),
        'role' => $user->getBadgeByUserType(),
      ];
    }
    return $usersList;
  }

//    /**
//     * @return Country[] Returns an array of Country objects
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

//    public function findOneBySomeField($value): ?Country
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
