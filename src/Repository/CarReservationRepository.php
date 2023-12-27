<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<CarReservation>
 *
 * @method CarReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarReservation[]    findAll()
 * @method CarReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarReservationRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, CarReservation::class);
    $this->security = $security;
  }

  public function save(CarReservation $carReservation): CarReservation {

    $car = $carReservation->getCar();
    $driver = $carReservation->getDriver();

    if (is_null($carReservation->getFinished())) {
      $car->setIsReserved(true);
      $driver->setCar($car->getId());
    } else {
      $car->setIsReserved(false);
      $car->setKm($carReservation->getKmStop());
      $driver->setCar(null);
    }

    $this->getEntityManager()->getRepository(Car::class)->save($car);
    $this->getEntityManager()->getRepository(User::class)->save($driver);

    if (is_null($carReservation->getId())) {
      $this->getEntityManager()->persist($carReservation);
    }

    $this->getEntityManager()->flush();

    return $carReservation;
  }

  public function countReservationByCar(Car $car): int {
    $qb = $this->createQueryBuilder('e');

    $qb->select($qb->expr()->count('e'))
      ->andWhere('e.car = :car')
      ->setParameter(':car', $car);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function remove(CarReservation $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }


  public function findForFormCar(Car $car = null): CarReservation {

    $reservation = new CarReservation();
    $reservation->setCar($car);
    $reservation->setKmStart($car->getKm());
    $reservation->setCompany($this->security->getUser()->getCompany());
    return $reservation;

  }

  public function findForForm(int $id = 0): CarReservation {
    if (empty($id)) {
      $tool = new CarReservation();
      $tool->setCompany($this->security->getUser()->getCompany());
      return $tool;
    }
    return $this->getEntityManager()->getRepository(CarReservation::class)->find($id);

  }

  public function getReservationsByUserPaginator(User $user) {

    return $this->createQueryBuilder('u')
      ->where('u.driver = :driver')
      ->setParameter('driver', $user)
      ->orderBy('u.id', 'DESC')
      ->getQuery();

  }

  public function getReservationsByCarPaginator(Car $car) {

    return $this->createQueryBuilder('u')
      ->where('u.car = :car')
      ->setParameter('car', $car)
      ->orderBy('u.id', 'DESC')
      ->getQuery();

  }

//    /**
//     * @return CarReservation[] Returns an array of CarReservation objects
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

//    public function findOneBySomeField($value): ?CarReservation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
