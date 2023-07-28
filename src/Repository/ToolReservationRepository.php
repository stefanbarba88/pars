<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\ToolReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolReservation>
 *
 * @method ToolReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolReservation[]    findAll()
 * @method ToolReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolReservationRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ToolReservation::class);
  }

  public function save(ToolReservation $toolReservation): ToolReservation {

    $tool = $toolReservation->getTool();

    if (is_null($toolReservation->getFinished())) {
      $tool->setIsReserved(true);
    } else {
      $tool->setIsReserved(false);
    }

    $this->getEntityManager()->getRepository(Tool::class)->save($tool);

    if (is_null($toolReservation->getId())) {
      $this->getEntityManager()->persist($toolReservation);
    }

    $this->getEntityManager()->flush();

    return $toolReservation;
  }

  public function remove(ToolReservation $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }



  public function findForFormTool(Tool $tool = null): ToolReservation {

    $reservation = new ToolReservation();
    $reservation->setTool($tool);
    return $reservation;

  }

  public function findForForm(int $id = 0): ToolReservation {
    if (empty($id)) {
      return new ToolReservation();
    }
    return $this->getEntityManager()->getRepository(ToolReservation::class)->find($id);

  }

//    /**
//     * @return ToolReservation[] Returns an array of ToolReservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ToolReservation
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}