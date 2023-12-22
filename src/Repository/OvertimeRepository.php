<?php

namespace App\Repository;

use App\Entity\Overtime;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Overtime>
 *
 * @method Overtime|null find($id, $lockMode = null, $lockVersion = null)
 * @method Overtime|null findOneBy(array $criteria, array $orderBy = null)
 * @method Overtime[]    findAll()
 * @method Overtime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OvertimeRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Overtime::class);
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
      return new Overtime();
    }
    return $this->getEntityManager()->getRepository(Overtime::class)->find($id);
  }

  public function getOvertimePaginator() {

    return $this->createQueryBuilder('c')
      ->where('c.status = :status')
      ->setParameter('status', 0)
      ->orderBy('c.datum', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->getQuery();
  }

  public function getOvertimeArchivePaginator() {

    return $this->createQueryBuilder('c')
      ->where('c.status > :status')
      ->setParameter('status', 0)
      ->orderBy('c.datum', 'DESC')
      ->addOrderBy('c.id', 'DESC')
      ->getQuery();
  }

  public function getOvertimeByUser(User $user): string {
    $sati = 0;
    $minuti = 0;

    $overtimes = $this->createQueryBuilder('c')
      ->where('c.status = :status')
      ->andWhere('c.user = :user')
      ->setParameter('status', 1)
      ->setParameter('user', $user)
      ->getQuery()
      ->getResult();

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
