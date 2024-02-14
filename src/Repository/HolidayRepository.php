<?php

namespace App\Repository;

use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Holiday;
use App\Entity\User;
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

  public function getHolidaysPaginator($year) {

    $company = $this->security->getUser()->getCompany();

    $startDate = new DateTimeImmutable("$year-01-01");
    $endDate = new DateTimeImmutable("$year-12-31");

    return $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->orderBy('c.datum', 'ASC')
      ->addOrderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();
  }

  public function getDostupnostHoliday($year): array {
    $company = $this->security->getUser()->getCompany();
    $dostupnost = [];

    $startDate = new DateTimeImmutable("$year-01-01");
    $endDate = new DateTimeImmutable("$year-12-31");

    $dostupnosti = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->orderBy('c.datum', 'ASC')
      ->addOrderBy('c.isSuspended', 'ASC')
      ->addOrderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {

        $color = '#c4dfea';
        if($dost->getType() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $color = '#00233d';
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
          "text" => '#00233F'
        ];
      }
    }

    return $dostupnost;
  }

//broj radnih dana u mesecu
  public function brojRadnihDanaMesecu(): int {
    $company = $this->security->getUser()->getCompany();

    $trenutniDatum = new DateTimeImmutable();

    $brojDana = $trenutniDatum->format('t');
    $brojNeradnihMesecu = $this->brojNeradnihDanaMesecu();

    $startDate = new DateTimeImmutable('first day of this month');

    $brojRadnihDana = 0;

    for ($i = 0; $i < $brojDana; $i++) {

      if ($startDate->format('N') < $company->getWorkWeek()) {
        $brojRadnihDana++;
      }

      $startDate = $startDate->modify("+1 day");
    }

    return $brojRadnihDana - $this->brojNeradnihDanaMesecu();
  }
  public function brojRadnihDanaTrenutno(): int {
    $company = $this->security->getUser()->getCompany();

    $brojNeradnihMesecu = $this->brojNeradnihDanaTrenutno();
    $trenutniDatum = new DateTimeImmutable();
    $startDate = new DateTimeImmutable('first day of this month');

    $brojDana = $trenutniDatum->modify('+2 day')->diff($startDate)->days;

    $brojRadnihDana = 0;
    for ($i = 0; $i < $brojDana; $i++) {
      if ($startDate->format('N') < $company->getWorkWeek()) {
        $brojRadnihDana++;
      }
      $startDate = $startDate->modify("+1 day");
    }
    return $brojRadnihDana - $this->brojNeradnihDanaTrenutno();
  }
  public function brojNeradnihDanaMesecu(): int {
    $company = $this->security->getUser()->getCompany();

    $startDate = new DateTimeImmutable("first day of this month");
    $startDate = $startDate->setTime(0,0);
    $endDate = new DateTimeImmutable('last day of this month');
    $endDate = $endDate->setTime(23, 59);

    $noPraznici = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('(
        c.type = :praznik OR
        c.type = :odmor
    )')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('praznik', TipNeradnihDanaData::PRAZNIK)
      ->setParameter('odmor', TipNeradnihDanaData::KOLEKTIVNI_ODMOR)
      ->getQuery()
      ->getResult();

    $praznici = 0;

    if (count($noPraznici) > 0 ) {
      foreach ($noPraznici as $praz) {
        if ($praz->getDatum()->format('N') < $company->getWorkWeek()) {
          $praznici++;
        }
      }
    }

    return $praznici;
  }
  public function brojNeradnihDanaTrenutno(): int {
    $company = $this->security->getUser()->getCompany();

    $startDate = new DateTimeImmutable("first day of this month");
    $startDate = $startDate->setTime(0,0);
    $endDate = new DateTimeImmutable();
    $endDate = $endDate->setTime(23, 59);

    $noPraznici = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('(
        c.type = :praznik OR
        c.type = :odmor
    )')
      ->andWhere('c.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('praznik', TipNeradnihDanaData::PRAZNIK)
      ->setParameter('odmor', TipNeradnihDanaData::KOLEKTIVNI_ODMOR)
      ->getQuery()
      ->getResult();

    $praznici = 0;

    if (count($noPraznici) > 0 ) {
      foreach ($noPraznici as $praz) {
        if ($praz->getDatum()->format('N') < $company->getWorkWeek()) {
          $praznici++;
        }
      }
    }

    return $praznici;
  }


  public function brojRadnihDanaDoJuce(): int {

    $company = $this->security->getUser()->getCompany();
//    $danas = time();  // Trenutni timestamp
//    $pocetakGodine = strtotime(date('Y-01-01'));  // Početak godine timestamp
//
//    $brojRadnihDana = 0;
//
//    // Petlja kroz svaki dan između početka godine i juče
//    for ($dan = $pocetakGodine; $dan < $danas; $dan += 86400) {  // 86400 sekundi u danu
//      // Proveri da li je trenutni dan radni dan i nije nedelja
//      if (date('N', $dan) < 6) {
//        $brojRadnihDana++;
//      }
//    }
    $danas = time();  // Trenutni timestamp
    $pocetakGodine = strtotime(date('Y-01-01'));  // Početak godine timestamp

    // Kreiraj DateTime objekte za današnji datum i početak godine
    $danasObj = new DateTimeImmutable(date('Y-m-d', $danas));
    $pocetakGodineObj = new DateTimeImmutable(date('Y-m-d', $pocetakGodine));

    // Izračunaj razliku između današnjeg datuma i početka godine
    $razlika = date_diff($pocetakGodineObj, $danasObj);

    // Uzmi broj dana iz razlike
    $brojDana = $razlika->days;

    // Inicijalizuj broj radnih dana
    $brojRadnihDana = 0;

    // Petlja kroz svaki dan između početka godine i juče
    for ($i = 0; $i < $brojDana; $i++) {
      // Kreiraj DateTime objekat za trenutni dan

      // Proveri da li je trenutni dan radni dan i nije nedelja
      if ($pocetakGodineObj->format('N') < $company->getWorkWeek()) {
        $brojRadnihDana++;
      }

      $pocetakGodineObj = $pocetakGodineObj->modify("+1 day");
    }
//dd($brojRadnihDana);
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

    $praznici = 0;

    if (count($noPraznici) > 0 ) {
      foreach ($noPraznici as $praz) {
        if ($praz->getDatum()->format('N') < $company->getWorkWeek()) {
          $praznici++;
        }
      }
    }

    return $praznici;
  }

  public function brojRadnihDanaDoJuceUser(User $user): int {

    $company = $user->getCompany();

    $danas = time();  // Trenutni timestamp

    $danasObj = new DateTimeImmutable(date('Y-m-d', $danas));
    $pocetakGodineObj = $user->getCreated()->setTime(0,0);

    // Izračunaj razliku između današnjeg datuma i početka godine
    $razlika = date_diff($pocetakGodineObj, $danasObj);

    // Uzmi broj dana iz razlike
    $brojDana = $razlika->days;

    // Inicijalizuj broj radnih dana
    $brojRadnihDana = 0;

    // Petlja kroz svaki dan između početka godine i juče
    for ($i = 0; $i < $brojDana; $i++) {
      // Proveri da li je trenutni dan radni dan i nije nedelja
      if ($pocetakGodineObj->format('N') < $company->getWorkWeek()) {
        $brojRadnihDana++;
      }
      $pocetakGodineObj = $pocetakGodineObj->modify("+1 day");
    }


    return $brojRadnihDana - $this->brojNeradnihDanaUser($user);
  }
  public function brojNeradnihDanaUser(User $user): int {

    $company = $user->getCompany();

    $startDate = $user->getCreated()->setTime(0,0);
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

    $praznici = 0;

    if (count($noPraznici) > 0 ) {
      foreach ($noPraznici as $praz) {
        if ($praz->getDatum()->format('N') < $company->getWorkWeek()) {
          $praznici++;
        }
      }
    }

    return $praznici;
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
