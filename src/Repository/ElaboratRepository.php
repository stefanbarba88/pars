<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Elaborat;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Elaborat>
 *
 * @method Elaborat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Elaborat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Elaborat[]    findAll()
 * @method Elaborat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElaboratRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Elaborat::class);
  }

  public function save(Elaborat $elaborat): void {
    $this->getEntityManager()->persist($elaborat);
    $this->getEntityManager()->flush();
  }

  public function remove(Elaborat $elaborat): void {
    $this->getEntityManager()->remove($elaborat);
    $this->getEntityManager()->flush();
  }

  public function getElaboratsByUserPaginator(User $user) {
    $queryBuilder = $this->createQueryBuilder('c');
    $queryBuilder->addSelect(
      'CASE WHEN c.status = 4 THEN 1 ELSE 0 END AS HIDDEN status_sort' // Sortiranje statusa (4 ide na kraj)
    );

    $queryBuilder
      ->orderBy('status_sort', 'ASC')        // Prvo sortiranje po status_sort
      ->addOrderBy('c.deadline', 'ASC')     // Zatim sortiranje po deadline
      ->addOrderBy('c.priority', 'ASC');    // Na kraju sortiranje po prioritetu

    // Filtriranje za zaposlenog korisnika
    if ($user->getUserType() === UserRolesData::ROLE_EMPLOYEE) {
      $queryBuilder
        ->where(':user MEMBER OF c.employee OR c.createdBy = :user') // Provera da li je korisnik u kolekciji `employee`
        ->setParameter('user', $user);
    }

    return $queryBuilder->getQuery()->getResult();
  }
  public function getElaboratsByUserPaginatorHome(User $user) {
    $queryBuilder = $this->createQueryBuilder('c');
    $queryBuilder->addSelect(
      'CASE WHEN c.status = 4 THEN 1 ELSE 0 END AS HIDDEN status_sort' // Sortiranje statusa (4 ide na kraj)
    );
    $queryBuilder->where('c.status <> 4');
    $queryBuilder
      ->orderBy('status_sort', 'ASC')        // Prvo sortiranje po status_sort
      ->addOrderBy('c.deadline', 'ASC')     // Zatim sortiranje po deadline
      ->addOrderBy('c.priority', 'ASC');    // Na kraju sortiranje po prioritetu

    // Filtriranje za zaposlenog korisnika
    if ($user->getUserType() === UserRolesData::ROLE_EMPLOYEE) {
      $queryBuilder
        ->where(':user MEMBER OF c.employee OR c.createdBy = :user')
        ->andWhere('c.status <> 4')// Provera da li je korisnik u kolekciji `employee`
        ->setParameter('user', $user);
    }

    $queryBuilder->setMaxResults(5);

    return $queryBuilder->getQuery()->getResult();
  }



  public function findForForm(int $id = 0): Elaborat {
    if (empty($id)) {
      return new Elaborat();
    }
    return $this->getEntityManager()->getRepository(Elaborat::class)->find($id);
  }

  public function getDeadlineCounter(int $id): array {

    $elaborat = $this->getEntityManager()->getRepository(Elaborat::class)->find($id);

    $poruka = '';
    $klasa = '';
    $klasa1 = '';
    $now = new DateTimeImmutable();
    $now->setTime(0,0);

    if (!is_null($elaborat->getDeadline())) {
      $endDate = $elaborat->getDeadline();
      // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
      $days = (int) $now->diff($endDate)->format('%R%a');

      if ($days > 0 && $days < 15 && $elaborat->getStatus() != 4) {
        $poruka = 'Rok za predaju je za ' . $days . ' dana.';
        $klasa = 'bg-warning bg-opacity-25';
        $klasa1 = 'alert alert-warning fade show';
      } elseif ($days == 0 && $elaborat->getStatus() != 4) {
        $poruka = 'Rok za predaju je danas.';
        $klasa = 'bg-danger bg-opacity-25';
        $klasa1 = 'alert alert-danger fade show';
      } elseif ($days < 0 && $elaborat->getStatus() != 4) {
        $poruka = 'Rok za predaju je istekao pre ' . abs($days) . ' dana.';
        $klasa = 'bg-danger bg-opacity-50';
        $klasa1 = 'alert alert-danger fade show';
      }
    }

    return [
      'klasa' => $klasa,
      'poruka' => $poruka,
      'klasa1' => $klasa1,
    ];
  }
//    /**
//     * @return Elaborat[] Returns an array of Elaborat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Elaborat
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
