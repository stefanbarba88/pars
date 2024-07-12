<?php

namespace App\Repository;

use App\Entity\Tool;
use App\Entity\ToolReservation;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<ToolReservation>
 *
 * @method ToolReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolReservation[]    findAll()
 * @method ToolReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolReservationRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, ToolReservation::class);
    $this->security = $security;
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
    $reservation->setCompany($this->security->getUser()->getCompany());
    return $reservation;

  }

  public function findForForm(int $id = 0): ToolReservation {
    if (empty($id)) {
      $tool = new ToolReservation();
      $tool->setCompany($this->security->getUser()->getCompany());
      return $tool;
    }
    return $this->getEntityManager()->getRepository(ToolReservation::class)->find($id);

  }

  public function getReservationsByUserPaginator(User $user) {

    return $this->createQueryBuilder('u')
      ->where('u.user = :user')
      ->setParameter('user', $user)
      ->orderBy('u.id', 'DESC')
      ->getQuery();

  }
  public function getReservationsByToolPaginator(Tool $tool) {

    return $this->createQueryBuilder('u')
      ->where('u.tool = :tool')
      ->setParameter('tool', $tool)
      ->orderBy('u.id', 'DESC')
      ->getQuery();

  }
  public function getReport(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);


    $tool = $this->getEntityManager()->getRepository(Tool::class)->find($data['oprema']);


    $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

    return $this->createQueryBuilder('t')
      ->where('t.created BETWEEN :startDate AND :endDate')
      ->andWhere('t.tool = :tool')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('tool', $tool)
      ->orderBy('t.created', 'DESC')
      ->getQuery()
      ->getResult();

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
