<?php

namespace App\Repository;

use App\Entity\Overtime;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Overtime>
 *
 * @method Overtime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Overtime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Overtime[]    findAll()
 * @method Overtime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OvertimeRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Overtime::class);
    $this->security = $security;
  }

  public function save(Overtime $overtime): Overtime {
    if (is_null($overtime->getId())) {
      $this->getEntityManager()->persist($overtime);
    }

    $this->getEntityManager()->flush();
    return $overtime;
  }

  public function remove(Overtime $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Overtime {
    if (empty($id)) {
      $overtime =  new Overtime();
      $overtime->setCompany($this->security->getUser()->getCompany());
      return $overtime;
    }
    return $this->getEntityManager()->getRepository(Overtime::class)->find($id);
  }

  public function getOvertimePaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->leftJoin('c.user', 'u') // Spajanje sa entitetom korisnika
      ->leftJoin('c.task', 't')
      ->where('c.status = :status')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('status', 0)
      ->orderBy('c.datum', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->getQuery();
  }

    public function checkOvertime(User $user): array {
        $nowMinus30 = new \DateTime('-30 seconds');

        return $this->createQueryBuilder('c')
            ->where(':user = c.user')
            ->andWhere('c.created > :nowMinus30')
            ->setParameter('user', $user)
            ->setParameter('nowMinus30', $nowMinus30)
            ->getQuery()
            ->getResult();
    }

//  public function getOvertimePaginator() {
//    $company = $this->security->getUser()->getCompany();
//    return $this->createQueryBuilder('c')
//      ->where('c.status = :status')
//      ->andWhere('c.company = :company')
//      ->setParameter('company', $company)
//      ->setParameter('status', 0)
//      ->orderBy('c.datum', 'DESC')
//      ->addOrderBy('c.id', 'DESC')
//      ->getQuery();
//  }

//  public function getOvertimeArchivePaginator() {
//    $company = $this->security->getUser()->getCompany();
//    return $this->createQueryBuilder('c')
//      ->where('c.status > :status')
//      ->andWhere('c.company = :company')
//      ->setParameter('company', $company)
//      ->setParameter('status', 0)
//      ->orderBy('c.datum', 'DESC')
//      ->addOrderBy('c.id', 'DESC')
//      ->getQuery();
//  }

  public function getOvertimeArchivePaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->leftJoin('c.user', 'u') // Spajanje sa entitetom korisnika
      ->leftJoin('c.task', 't')
      ->where('c.status > :status')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('status', 0)
      ->orderBy('c.datum', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->getQuery();
  }

  public function getOvertimeByUser(User $user): string {
    $yearStart = new DateTimeImmutable(date('Y-01-01'));
    $sati = 0;
    $minuti = 0;
    $overtimes = $this->createQueryBuilder('c')
      ->where('c.status = :status')
      ->andWhere('c.user = :user')
      ->andWhere('c.datum >= :yearStart')  // Dodajte uvjet za datum
      ->setParameter('status', 1)
      ->setParameter('user', $user)
      ->setParameter('yearStart', $yearStart)
      ->getQuery()
      ->getResult();

//
//
//    $overtimes = $this->createQueryBuilder('c')
//      ->where('c.status = :status')
//      ->andWhere('c.user = :user')
//      ->setParameter('status', 1)
//      ->setParameter('user', $user)
//      ->getQuery()
//      ->getResult();

    if (!empty($overtimes)) {
      foreach ($overtimes as $over) {
        $sati = $sati + ($over->getHours() * 60);
        $minuti = $minuti + $over->getMinutes();
      }
    }

    $ukupno = $sati + $minuti;

    $sati = floor($ukupno / 60);
    $minuti = $ukupno % 60;

    return sprintf("%02d:%02d", $sati, $minuti);

  }
  public function getOvertimeByUserMesec(User $user, DateTimeImmutable $date): array {

//    $startDate = $date->modify('first day of this month')->setTime(0, 0);
//    $endDate = $date->modify('last day of this month')->setTime(23, 59);

    $startDate = $date->modify('first day of last month')->setTime(0, 0);
    $endDate = $date->modify('last day of last month')->setTime(23, 59);

    $sati = 0;
    $minuti = 0;
    $overtimes = $this->createQueryBuilder('c')
      ->where('c.status = :status')
      ->andWhere('c.user = :user')
      ->andWhere('c.datum BETWEEN :startDate AND :endDate')  // Dodajte uvjet za datum
      ->setParameter('status', 1)
      ->setParameter('user', $user)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->getQuery()
      ->getResult();

//
//
//    $overtimes = $this->createQueryBuilder('c')
//      ->where('c.status = :status')
//      ->andWhere('c.user = :user')
//      ->setParameter('status', 1)
//      ->setParameter('user', $user)
//      ->getQuery()
//      ->getResult();

    if (!empty($overtimes)) {
      foreach ($overtimes as $over) {
        $sati = $sati + ($over->getHours() * 60);
        $minuti = $minuti + $over->getMinutes();
      }
    }

    $ukupno = $sati + $minuti;

    $sati = floor($ukupno / 60);
    $minuti = $ukupno % 60;

    return [sprintf("%02d:%02d", $sati, $minuti), $overtimes];

  }

//    /**
//     * @return Overtime[] Returns an array of Overtime objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Overtime
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
