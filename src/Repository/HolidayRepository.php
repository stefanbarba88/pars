<?php

namespace App\Repository;

use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Holiday;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Holiday>
 *
 * @method Holiday|null find($id, $lockMode = null, $lockVersion = null)
 * @method Holiday|null findOneBy(array $criteria, array $orderBy = null)
 * @method Holiday[]    findAll()
 * @method Holiday[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HolidayRepository extends ServiceEntityRepository {
  private $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Holiday::class);
    $this->security = $security;
  }

  public function save(Holiday $holiday): Holiday {
    if (is_null($holiday->getId())) {
      $this->getEntityManager()->persist($holiday);
    }

    $this->getEntityManager()->flush();
    return $holiday;
  }

  public function remove(Holiday $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Holiday {
    if (empty($id)) {
      $holiday =  new Holiday();
      $holiday->setCompany($this->security->getUser()->getCompany());
      return $holiday;

    }
    return $this->getEntityManager()->getRepository(Holiday::class)->find($id);
  }

  public function getHolidaysPaginator() {

    $company = $this->security->getUser()->getCompany();

//    $startDate = new DateTimeImmutable("$year-01-01");
//    $endDate = new DateTimeImmutable("$year-12-31");

    return $this->createQueryBuilder('c')
//      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
//      ->setParameter('startDate', $startDate)
//      ->setParameter('endDate', $endDate)
      ->orderBy('c.datum', 'ASC')
      ->addOrderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();
  }

  public function getDostupnostHoliday(): array {
    $company = $this->security->getUser()->getCompany();
    $dostupnost = [];

//    $startDate = new DateTimeImmutable("$year-01-01");
//    $endDate = new DateTimeImmutable("$year-12-31");

    $dostupnosti = $this->createQueryBuilder('c')
//      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
//      ->setParameter('startDate', $startDate)
//      ->setParameter('endDate', $endDate)
      ->orderBy('c.datum', 'ASC')
      ->addOrderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {

        $color = '#EF4443';
        if($dost->getType() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $color = '#F48445';
        }

        $dostupnost[] = [
          "title" => $dost->getTitle(),
          "start" => $dost->getDatum()->format('Y-m-d'),
          "datum" => $dost->getDatum()->format('d.m.Y'),
          "color" => $color,
          "name" => $dost->getTitle(),
          "id" => '',
          "zahtev" => '',
          "razlog" => '',
          "text" => '#FFF'
        ];
      }
    }

    return $dostupnost;
  }

  public function brojRadnihDanaDoJuce(): int {
    $danas = time();  // Trenutni timestamp
    $pocetakGodine = strtotime(date('Y-01-01'));  // Početak godine timestamp

    $brojRadnihDana = 0;

    // Petlja kroz svaki dan između početka godine i juče
    for ($dan = $pocetakGodine; $dan < $danas; $dan += 86400) {  // 86400 sekundi u danu
      // Proveri da li je trenutni dan radni dan i nije nedelja
      if (date('N', $dan) < 6) {
        $brojRadnihDana++;
      }
    }

    return $brojRadnihDana - $this->brojNeradnihDana();
  }

  public function brojNeradnihDana(): int {
    $company = $this->security->getUser()->getCompany();
    $year = date('Y');
    $startDate = new DateTimeImmutable("$year-01-01");
    $endDate = new DateTimeImmutable();

    $noPraznici = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.type = :praznik')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('praznik', TipNeradnihDanaData::PRAZNIK)
      ->getQuery()
      ->getResult();



    return count($noPraznici);
  }

//    /**
//     * @return Holiday[] Returns an array of Holiday objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Holiday
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
