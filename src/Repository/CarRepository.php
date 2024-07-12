<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\CarHistory;
use App\Entity\CarReservation;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Car>
 *
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Car::class);
    $this->security = $security;
  }

  public function saveHistory(Car $car, ?string $history): Car {
    $historyCar = new CarHistory();
    $historyCar->setHistory($history);

    $car->addCarHistory($historyCar);

    return $car;
  }

  public function save(Car $car, ?string $history = null): Car {

    if (!is_null($history)) {
      $this->saveHistory($car, $history);
    }

    if (is_null($car->getId())) {
      $car->setCreatedBy($this->security->getUser());
      $this->getEntityManager()->persist($car);
    } else {
      $car->setEditBy($this->security->getUser());
    }

    $this->getEntityManager()->flush();

    return $car;
  }


  public function remove(Car $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }


  public function findForForm(int $id = 0): Car {
    if (empty($id)) {
      $car = new Car();
      $car->setCompany($this->security->getUser()->getCompany());
      return $car;
    }
    return $this->getEntityManager()->getRepository(Car::class)->find($id);
  }

  public function getCarsKm(): array {
    $company = $this->security->getUser()->getCompany();
    $vozila = [];
    $cars = $this->getEntityManager()->getRepository(Car::class)->findBy(['isSuspended' => false, 'company' => $company], ['id' => 'ASC']);
    foreach ($cars as $car) {
      if (is_null($car->getKm())) {
        $min = 0;
      } else {
        $min = $car->getKm();
      }
      $vozila [] = [
        'id' => $car->getId(),
        'minKm' => $min
      ];
    }
    return $vozila;
  }

  public function getCarRegistration(Company $company): array {

    $datum = new DateTimeImmutable();
    $danas = $datum->setTime(0,0);

    $vozila = [
      30 => [],
      10 => [],
      3 => [],
      2 => [],
      1 => [],
      0 => [],
    ];
    $cars = $this->getEntityManager()->getRepository(Car::class)->findBy(['isSuspended' => false, 'company' => $company], ['id' => 'ASC']);

    foreach ($cars as $car) {

      if ($danas->diff($car->getDatumNaredneRegistracije())->days == 30) {
        $vozila[30] = [$car];
      } elseif ($danas->diff($car->getDatumNaredneRegistracije())->days == 10) {
        $vozila[10] = [$car];
      } elseif ($danas->diff($car->getDatumNaredneRegistracije())->days == 3) {
        $vozila[3] = [$car];
      } elseif ($danas->diff($car->getDatumNaredneRegistracije())->days == 2) {
        $vozila[2] = [$car];
      } elseif ($danas->diff($car->getDatumNaredneRegistracije())->days == 1) {
        $vozila[1] = [$car];
      } elseif ($danas->diff($car->getDatumNaredneRegistracije())->days == 0) {
        $vozila[0] = [$car];
      }
    }

    return $vozila;
  }


  public function getCarsPaginator($filter, $suspended) {
    $company = $this->security->getUser()->getCompany();

    $qb =  $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->andWhere('c.isSuspended = :suspenzija')
      ->setParameter('suspenzija', $suspended);

    if (!empty($filter['naziv'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.brand', ':naziv'),
      ))
        ->setParameter('naziv', '%' . $filter['naziv'] . '%');
    }
    if (!empty($filter['registracija'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.plate', ':registracija'),
      ))
        ->setParameter('registracija', '%' . $filter['registracija'] . '%');
    }


    $qb->orderBy('c.isReserved', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();

    return $qb;
  }

  public function getCarsReservedPaginator($filter, $reserved) {
    $company = $this->security->getUser()->getCompany();

    $qb =  $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->andWhere('c.isReserved = :reserved')
      ->setParameter('reserved', $reserved);

    if (!empty($filter['naziv'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.brand', ':naziv'),
      ))
        ->setParameter('naziv', '%' . $filter['naziv'] . '%');
    }
    if (!empty($filter['registracija'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.plate', ':registracija'),
      ))
        ->setParameter('registracija', '%' . $filter['registracija'] . '%');
    }


    $qb
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();

    return $qb;
  }

//    /**
//     * @return Car[] Returns an array of Car objects
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

//    public function findOneBySomeField($value): ?Car
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
