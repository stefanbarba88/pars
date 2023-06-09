<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\CarReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarReservation>
 *
 * @method CarReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarReservation[]    findAll()
 * @method CarReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarReservationRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, CarReservation::class);
  }

  public function save(CarReservation $entity, bool $flush = false): void {
    $this->getEntityManager()->persist($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
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
    return $reservation;

  }

  public function findForForm(int $id = 0): CarReservation {
    if (empty($id)) {
      return new CarReservation();
    }
    return $this->getEntityManager()->getRepository(CarReservation::class)->find($id);

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
