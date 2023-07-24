<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\CarHistory;
use App\Entity\CarReservation;
use App\Entity\FastTask;
use App\Entity\User;
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
      return new Car();
    }
    return $this->getEntityManager()->getRepository(Car::class)->find($id);
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
