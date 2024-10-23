<?php

namespace App\Repository;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Classes\Data\TipProjektaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Company;
use App\Entity\FastTask;
use App\Entity\Holiday;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Array_;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Availability>
 *
 * @method Availability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Availability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Availability[]    findAll()
 * @method Availability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvailabilityRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Availability::class);
    $this->security = $security;
  }

  public function save(Availability $availability): Availability {

    if (is_null($availability->getId())) {
      $this->getEntityManager()->persist($availability);
    }

    $this->getEntityManager()->flush();
    return $availability;
  }

  public function getReport(array $data, Company $company): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $report = [];
    $users = [];
    $tipovi = [];

    if (!empty($data['zaposleni'])) {
      $users[]= $this->getEntityManager()->getRepository(User::class)->find($data['zaposleni']);
    } else {
      $users = $this->getEntityManager()->getRepository(User::class)->findBy(['company' => $company, 'userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false], ['prezime' => 'ASC']);
    }

//    if (isset($data['category'])) {
//      foreach ($data['category'] as $tip) {
//        $tipovi [] = $tip;
//      }
//    }

    foreach ($users as $user) {
      $report[] = $this->getDaysByDate($user, $start, $stop, $tipovi);
    }

    return $report;


  }

//  public function getDaysByDate(User $user, $start, $stop, $tipovi): array {
  public function getDaysByDate(User $user, $start, $stop): array {

    $nedostupan = 0;
    $izasao = 0;
    $dostupan = 0;

    $praznik = 0;
    $nedelja = 0;
    $kolektivniOdmor = 0;

    $neradniPraznik = 0;
    $neradnaNedelja = 0;
    $neradniKolektivniOdmor = 0;
    $nemaMerenje = 0;

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;
    $ostalo = 0;
//    if (!empty($tipovi)) {
//      $requests = $this->createQueryBuilder('c')
//        ->where('c.datum BETWEEN :startDate AND :endDate')
//        ->andWhere('c.User = :user')
//        ->andWhere('c.type IN (:types)')
//        ->setParameter('startDate', $start)
//        ->setParameter('endDate', $stop)
//        ->setParameter('user', $user)
//        ->setParameter('types', $tipovi)
//        ->getQuery()
//        ->getResult();
//    } else {
      $requests = $this->createQueryBuilder('c')
        ->where('c.datum BETWEEN :startDate AND :endDate')
        ->andWhere('c.User = :user')
        ->setParameter('startDate', $start)
        ->setParameter('endDate', $stop)
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
//    }

    foreach ($requests as $req) {
      if ($req->getType() == AvailabilityData::PRISUTAN) {
        $dostupan++;
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $praznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $kolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $nedelja++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
          $nedelja++;
          $praznik++;
        }
      }
      if ($req->getType() == AvailabilityData::IZASAO) {
        $izasao++;
        $dostupan++;
      }
      if ($req->getType() == AvailabilityData::NEDOSTUPAN) {
        $nedostupan++;

        if ($req->getZahtev() == CalendarColorsData::DAN) {
          $dan++;
        }
        if ($req->getZahtev() == CalendarColorsData::ODMOR) {
          $odmor++;
        }
        if ($req->getZahtev() == CalendarColorsData::BOLOVANJE) {
          $bolovanje++;
        }
        if ($req->getZahtev() == CalendarColorsData::SLAVA) {
          $slava++;
        }
        if (is_null($req->getZahtev())) {
          $ostalo++;
          if ($req->getTypeDay() == TipNeradnihDanaData::RADNI_DAN) {
            $nemaMerenje++;
          };
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $neradniPraznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $neradniKolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $neradnaNedelja++;
        }
      }
    }

    return  [
      'dostupan' => $dostupan,
      'praznik' => $praznik,
      'kolektivniOdmor' => $kolektivniOdmor,
      'nedelja' => $nedelja,
      'izasao' => $izasao,
      'nedostupan' => $nedostupan,
      'nedostupanBezNedelje' => $nedostupan - $neradnaNedelja,
      'nedostupanProfil' => $nedostupan - $neradnaNedelja - $neradniPraznik,
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ostalo' => $ostalo,
      'neradniPraznik' => $neradniPraznik,
      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
      'neradnaNedelja' => $neradnaNedelja,
      'nemaMerenje' => $nemaMerenje,
      'ukupno' => $dan + $odmor + $bolovanje + $slava + $neradniKolektivniOdmor + $ostalo,
      'vacationData' => $user->getVacation(),
      'user' => $user->getFullName(),
    ];

  }

  public function getDays($filter, User $user) {
    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme
//    $endDate = $today->sub(new DateInterval('P1D')); // Trenutni datum
    $endDate = $today; // Trenutni datum

    $qb = $this->createQueryBuilder('u');
    $qb->where('u.User = :user');
    $qb->setParameter('user', $user);

    if (!empty($filter['period'])) {

      $dates = explode(' - ', $filter['period']);
      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

      $qb->andWhere($qb->expr()->between('u.datum', ':start', ':end'));
      $qb->setParameter('start', $startDate);
      $qb->setParameter('end', $stopDate);

    } else {
      $qb->andWhere('u.datum < :endDate');
      $qb->setParameter('endDate', $endDate);
    }
    if (!empty($filter['tip'])) {
      $qb->andWhere('u.type = :type');
      $qb->setParameter('type', $filter['tip']);
    }
    return $qb
      ->orderBy('u.datum', 'DESC')
      ->getQuery();
  }

//  public function getDaysByUser(User $user, $year): array {
//
//    $nedostupan = 0;
//    $izasao = 0;
//    $dostupan = 0;
//
//    $praznik = 0;
//    $nedelja = 0;
//    $kolektivniOdmor = 0;
//
//    $neradniPraznik = 0;
//    $neradnaNedelja = 0;
//    $neradniKolektivniOdmor = 0;
//    $nemaMerenje = 0;
//
//    $dan = 0;
//    $odmor = 0;
//    $slava = 0;
//    $bolovanje = 0;
//    $ostalo = 0;
//
//    $startDate = new DateTimeImmutable("$year-01-01");
//    $endDate = new DateTimeImmutable("$year-12-31");
//
//    $requests = $this->createQueryBuilder('c')
//      ->where('c.datum BETWEEN :startDate AND :endDate')
//      ->andWhere('c.User = :user')
//      ->setParameter('startDate', $startDate)
//      ->setParameter('endDate', $endDate)
//      ->setParameter('user', $user)
//      ->getQuery()
//      ->getResult();
//
//    foreach ($requests as $req) {
//        if ($req->getType() == AvailabilityData::PRISUTAN) {
//          $dostupan++;
//          if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
//            $praznik++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//            $kolektivniOdmor++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
//            $nedelja++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
//            $nedelja++;
//            $praznik++;
//          }
//        }
//        if ($req->getType() == AvailabilityData::IZASAO) {
//          $izasao++;
//        }
//        if ($req->getType() == AvailabilityData::NEDOSTUPAN) {
//          $nedostupan++;
//
//          if ($req->getZahtev() == CalendarColorsData::DAN) {
//            $dan++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::ODMOR) {
//            $odmor++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::BOLOVANJE) {
//            $bolovanje++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::SLAVA) {
//            $slava++;
//          }
//          if (is_null($req->getZahtev())) {
//            $ostalo++;
//            if ($req->getTypeDay() == 0) {
//              $nemaMerenje++;
//            };
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
//            $neradniPraznik++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//            $neradniKolektivniOdmor++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
//            $neradnaNedelja++;
//          }
//        }
//    }
//
//    return  [
//      'dostupan' => $dostupan,
//      'praznik' => $praznik,
//      'kolektivniOdmor' => $kolektivniOdmor,
//      'nedelja' => $nedelja,
//      'izasao' => $izasao,
//      'nedostupan' => $nedostupan,
//      'nedostupanBezNedelje' => $nedostupan - $neradnaNedelja,
//      'dan' => $dan,
//      'odmor' => $odmor,
//      'bolovanje' => $bolovanje,
//      'slava' => $slava,
//      'ostalo' => $ostalo,
//      'neradniPraznik' => $neradniPraznik,
//      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
//      'neradnaNedelja' => $neradnaNedelja,
//      'nemaMerenje' => $nemaMerenje,
//      'ukupno' => $dan + $odmor + $bolovanje + $slava + $neradniKolektivniOdmor + $ostalo
//    ];
//
//  }
//  public function getDaysByUserUser(User $user, $year): array {
//
//    $nedostupan = 0;
//    $izasao = 0;
//    $dostupan = 0;
//
//    $praznik = 0;
//    $nedelja = 0;
//    $kolektivniOdmor = 0;
//
//    $neradniPraznik = 0;
//    $neradnaNedelja = 0;
//    $neradniKolektivniOdmor = 0;
//
//
//    $dan = 0;
//    $odmor = 0;
//    $slava = 0;
//    $bolovanje = 0;
//    $ostalo = 0;
//    $nemaMerenje = 0;
//
//    $startDate = new DateTimeImmutable("$year-01-01");
//    $endDate = new DateTimeImmutable();
//    $endDate->setTime(0,0);
//
//    $requests = $this->createQueryBuilder('c')
//      ->where('c.datum BETWEEN :startDate AND :endDate')
//      ->andWhere('c.User = :user')
//      ->setParameter('startDate', $startDate)
//      ->setParameter('endDate', $endDate)
//      ->setParameter('user', $user)
//      ->getQuery()
//      ->getResult();
//
//    foreach ($requests as $req) {
//        if ($req->getType() == AvailabilityData::PRISUTAN) {
//          $dostupan++;
//          if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
//            $praznik++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//            $kolektivniOdmor++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
//            $nedelja++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
//            $nedelja++;
//            $praznik++;
//          }
//        }
//        if ($req->getType() == AvailabilityData::IZASAO) {
//          $izasao++;
//        }
//        if ($req->getType() == AvailabilityData::NEDOSTUPAN) {
//          $nedostupan++;
//
//          if ($req->getZahtev() == CalendarColorsData::DAN) {
//            $dan++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::ODMOR) {
//            $odmor++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::BOLOVANJE) {
//            $bolovanje++;
//          }
//          if ($req->getZahtev() == CalendarColorsData::SLAVA) {
//            $slava++;
//          }
//          if (is_null($req->getZahtev())) {
//            $ostalo++;
//            if ($req->getTypeDay() == 0) {
//              $nemaMerenje++;
//            };
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
//            $neradniPraznik++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
//            $neradniKolektivniOdmor++;
//          }
//          if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
//            $neradnaNedelja++;
//          }
//        }
//    }
//
//    return  [
//      'dostupan' => $dostupan,
//      'praznik' => $praznik,
//      'kolektivniOdmor' => $kolektivniOdmor,
//      'nedelja' => $nedelja,
//      'izasao' => $izasao,
//      'nedostupan' => $nedostupan,
//      'nedostupanBezNedelje' => $nedostupan - $neradnaNedelja,
//      'dan' => $dan,
//      'odmor' => $odmor,
//      'bolovanje' => $bolovanje,
//      'slava' => $slava,
//      'ostalo' => $ostalo,
//      'neradniPraznik' => $neradniPraznik,
//      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
//      'neradnaNedelja' => $neradnaNedelja,
//      'nemaMerenje' => $nemaMerenje,
//      'ukupno' => $dan + $odmor + $bolovanje + $slava + $neradniKolektivniOdmor + $ostalo
//    ];
//
//  }

  public function getDaysByUser(User $user, $year): array {

    $nedostupan = 0;
    $izasao = 0;
    $dostupan = 0;

    $praznik = 0;
    $nedelja = 0;
    $kolektivniOdmor = 0;

    $neradniPraznik = 0;
    $neradnaNedelja = 0;
    $neradniKolektivniOdmor = 0;
    $nemaMerenje = 0;

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;
    $ostalo = 0;

    $startDate = new DateTimeImmutable("$year-01-01");
    $endDate = new DateTimeImmutable("$year-12-31");

    $requests = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.User = :user')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('user', $user)
      ->getQuery()
      ->getResult();

    foreach ($requests as $req) {
      if ($req->getType() == AvailabilityData::PRISUTAN) {
        $dostupan++;
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $praznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $kolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $nedelja++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
          $nedelja++;
          $praznik++;
        }
      }
      if ($req->getType() == AvailabilityData::IZASAO) {
        $izasao++;
        $dostupan++;
      }
      if ($req->getType() == AvailabilityData::NEDOSTUPAN) {
        $nedostupan++;

        if ($req->getZahtev() == CalendarColorsData::DAN) {
          $dan++;
        }
        if ($req->getZahtev() == CalendarColorsData::ODMOR) {
          $odmor++;
        }
        if ($req->getZahtev() == CalendarColorsData::BOLOVANJE) {
          $bolovanje++;
        }
        if ($req->getZahtev() == CalendarColorsData::SLAVA) {
          $slava++;
        }
        if (is_null($req->getZahtev())) {
          $ostalo++;
          if ($req->getTypeDay() == TipNeradnihDanaData::RADNI_DAN) {
            $nemaMerenje++;
          };
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $neradniPraznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $neradniKolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $neradnaNedelja++;
        }
      }
    }

    return  [
      'dostupan' => $dostupan,
      'praznik' => $praznik,
      'kolektivniOdmor' => $kolektivniOdmor,
      'nedelja' => $nedelja,
      'izasao' => $izasao,
      'nedostupan' => $nedostupan,
      'nedostupanBezNedelje' => $nedostupan - $neradnaNedelja,
      'nedostupanProfil' => $nedostupan - $neradnaNedelja - $neradniPraznik,
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ostalo' => $ostalo,
      'neradniPraznik' => $neradniPraznik,
      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
      'neradnaNedelja' => $neradnaNedelja,
      'nemaMerenje' => $nemaMerenje,
      'ukupno' => $dan + $odmor + $bolovanje + $slava + $neradniKolektivniOdmor + $ostalo
    ];

  }
  public function getDaysByUserUser(User $user, $year): array {

    $nedostupan = 0;
    $izasao = 0;
    $dostupan = 0;

    $praznik = 0;
    $nedelja = 0;
    $kolektivniOdmor = 0;

    $neradniPraznik = 0;
    $neradnaNedelja = 0;
    $neradniKolektivniOdmor = 0;


    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;
    $ostalo = 0;
    $nemaMerenje = 0;

    $startDate = new DateTimeImmutable("$year-01-01");
    $endDate = new DateTimeImmutable();
    $endDate->setTime(0,0);

    $requests = $this->createQueryBuilder('c')
      ->where('c.datum BETWEEN :startDate AND :endDate')
      ->andWhere('c.User = :user')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('user', $user)
      ->getQuery()
      ->getResult();

    foreach ($requests as $req) {
      if ($req->getType() == AvailabilityData::PRISUTAN) {
        $dostupan++;
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $praznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $kolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $nedelja++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
          $nedelja++;
          $praznik++;
        }
      }
      if ($req->getType() == AvailabilityData::IZASAO) {
        $izasao++;
        $dostupan++;
      }
      if ($req->getType() == AvailabilityData::NEDOSTUPAN) {
        $nedostupan++;

        if ($req->getZahtev() == CalendarColorsData::DAN) {
          $dan++;
        }
        if ($req->getZahtev() == CalendarColorsData::ODMOR) {
          $odmor++;
        }
        if ($req->getZahtev() == CalendarColorsData::BOLOVANJE) {
          $bolovanje++;
        }
        if ($req->getZahtev() == CalendarColorsData::SLAVA) {
          $slava++;
        }
        if (is_null($req->getZahtev())) {
          $ostalo++;
          if ($req->getTypeDay() == TipNeradnihDanaData::RADNI_DAN) {
            $nemaMerenje++;
          }
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::PRAZNIK) {
          $neradniPraznik++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $neradniKolektivniOdmor++;
        }
        if ($req->getTypeDay() == TipNeradnihDanaData::NEDELJA) {
          $neradnaNedelja++;
        }
      }
    }

    return  [
      'dostupan' => $dostupan,
      'praznik' => $praznik,
      'kolektivniOdmor' => $kolektivniOdmor,
      'nedelja' => $nedelja,
      'izasao' => $izasao,
      'nedostupan' => $nedostupan,
      'nedostupanBezNedelje' => $nedostupan - $neradnaNedelja,
      'nedostupanProfil' => $nedostupan - $neradnaNedelja - $neradniPraznik,
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ostalo' => $ostalo,
      'neradniPraznik' => $neradniPraznik,
      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
      'neradnaNedelja' => $neradnaNedelja,
      'nemaMerenje' => $nemaMerenje,
      'ukupno' => $dan + $odmor + $bolovanje + $slava + $neradniKolektivniOdmor + $ostalo
    ];

  }

  public function makeAvailable(User $user): void {

    $datum = new DateTimeImmutable();
    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana

    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.datum BETWEEN :startDate AND :endDate')
      ->andWhere('t.User = :user')
      ->andWhere('t.type <> :type')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('user', $user)
      ->setParameter('type', AvailabilityData::PRISUTAN)
      ->getQuery()
      ->getResult();


    $dostupnost = $dostupnosti[0];

    $this->remove($dostupnost);

  }

  public function makeUnavailable(User $user): Availability {

    $datum = new DateTimeImmutable();

    $dostupnost = new Availability();
    $dostupnost->setType(AvailabilityData::IZASAO);
    $dostupnost->setDatum($datum->setTime(0, 0));
    $dostupnost->setUser($user);
    $dostupnost->setCompany($user->getCompany());

    return $this->save($dostupnost);

  }

  public function addDostupnost(StopwatchTime $stopwatchTime): ?Availability {
    $datum = $stopwatchTime->getStart();

    $dostupnost = new Availability();
    $dostupnost->setType(AvailabilityData::PRISUTAN);
    $dostupnost->setDatum($datum->setTime(0, 0));
    $dostupnost->setUser($stopwatchTime->getTaskLog()->getUser());
    $dostupnost->setTask($stopwatchTime->getTaskLog()->getTask()->getId());

    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana

    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.datum BETWEEN :startDate AND :endDate')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->getQuery()
      ->getResult();

    if (empty($dostupnosti)) {
      $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
    } else {
      foreach ($dostupnosti as $dost) {
        if ($dost->getType() == AvailabilityData::NEDOSTUPAN) {
          $this->getEntityManager()->getRepository(Availability::class)->remove($dost);
          $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
        }
      }
    }
    return $dostupnost;
  }
  public function getDostupnost(): array {
    $company = $this->security->getUser()->getCompany();

    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.datum >= :danas')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':danas', $danas)
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        $dostupnost[] = [
          "title" => $dost->getUser()->getFullName(),
          "start" => $dost->getDatum()->format('Y-m-d'),
          "datum" => $dost->getDatum()->format('d.m.Y'),
          "color" => CalendarColorsData::getColorByType($dost->getZahtev()),
          "name" => $dost->getUser()->getFullName(),
          "id" => $dost->getUser()->getId(),
          "zahtev" => $dost->getZahtev(),
          "razlog" => CalendarColorsData::getTitleByType($dost->getZahtev()),
          "text" => CalendarColorsData::getTextByType($dost->getZahtev())
        ];
      }
    }
//    $dostupnost1 =  $this->getEntityManager()->getRepository(Holiday::class)->getDostupnostHoliday($datum->format('Y'));
    $dostupnost1 =  $this->getEntityManager()->getRepository(Holiday::class)->getDostupnostHoliday($datum->format('Y'));
//    $dostupnost1 =  $this->getEntityManager()->getRepository(Holiday::class)->getDostupnostHoliday();



    return array_merge($dostupnost, $dostupnost1);
  }

  public function getDostupnostPaginator() {
    $company = $this->security->getUser()->getCompany();
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.datum >= :danas')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':danas', $danas)
      ->orderBy('t.datum', 'ASC')
      ->getQuery();

    return $dostupnosti;

  }

  public function getDostupnostByUser(User $user): array {
    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.User = :user')
      ->setParameter(':user', $user->getId())
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        if (is_null($dost->getZahtev())) {
          $zahtev = 5;
        } else {
          $zahtev = $dost->getZahtev();
        }
        if ($dost->getDatum()->format('N') != 7) {
          $dostupnost[] = [
            "title" => CalendarColorsData::getTitleByType($zahtev),
            "start" => $dost->getDatum()->format('Y-m-d'),
            "datum" => $dost->getDatum()->format('d.m.Y'),
            "color" => CalendarColorsData::getColorByType($zahtev),
            "name" => $dost->getUser()->getFullName(),
            "id" => $dost->getUser()->getId(),
            "zahtev" => $dost->getZahtev(),
            "razlog" => CalendarColorsData::getTitleByType($zahtev),
            "text" => CalendarColorsData::getTextByType($zahtev)
          ];
        }
      }
    }

//    $dostupnost1 =  $this->getEntityManager()->getRepository(Holiday::class)->getDostupnostHoliday($datum->format('Y'));
    $dostupnost1 =  $this->getEntityManager()->getRepository(Holiday::class)->getDostupnostHoliday($datum->format('Y'));


    return array_merge($dostupnost, $dostupnost1);
  }

  public function getDostupnostByUserTwig(User $user): ?int {

    $datum = new DateTimeImmutable();
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.User = :user')
      ->andWhere('t.datum = :datum')
      ->setParameter(':user', $user->getId())
      ->setParameter(':datum', $datum->format('Y-m-d 00:00:00'))
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        if ($dost->getType() == 2) {
          return 0;
        } else {
          if (is_null($dost->getZahtev())) {
            return 5;
          } else {
            return $dost->getZahtev();
          }
        }
      }
    }
  return null;

  }


  public function getDostupnostByUserId(User $user, string $datum) {
    $return = [];
    $return['task'] = [];
    $return['code'] = 10;
    $datetime = DateTimeImmutable::createFromFormat('d.m.Y.', $datum);
    $formattedDate = $datetime->format('Y-m-d 00:00:00');

    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.User = :user')
      ->andWhere('t.datum = :datum')
      ->setParameter(':user', $user->getId())
      ->setParameter(':datum', $formattedDate)
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        if ($dost->getType() == 2) {
          $return['code'] = 0;
        } else {
          if (is_null($dost->getZahtev())) {
            $return['code'] = 5;
          } else {
            $return['code'] = $dost->getZahtev();
          }
        }
      }
    } else {
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByUserTwigCheck($user, $datetime);
      if (empty($tasks)) {
        $return['task'] = [];
        $return['code'] = 11;
      } else {
        foreach ($tasks as $task) {
          $return['task'][] = $task->getProject()->getTitle();
        }
      }
    }
    return $return;

  }

  public function getDostupnostByUserTwigSutra(User $user): ?int {

    $datum = new DateTimeImmutable();
    $sutra = $datum->add(new DateInterval('P1D'));
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.User = :user')
      ->andWhere('t.datum = :datum')
      ->setParameter(':user', $user->getId())
      ->setParameter(':datum', $sutra->format('Y-m-d 00:00:00'))
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        if ($dost->getType() == 2) {
          return 0;
        } else {
          if (is_null($dost->getZahtev())) {
            return 5;
          } else {
            return $dost->getZahtev();
          }
        }
      }
    }
    return null;

  }

  public function getDostupnostDanas(): array {
    $company = $this->security->getUser()->getCompany();
    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.datum = :danas')
      ->andWhere('t.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':danas', $danas)
      ->getQuery()
      ->getResult();

    if (!empty($dostupnosti)) {
      foreach ($dostupnosti as $dost) {
        $dostupnost[] = [
          "title" => $dost->getUser()->getFullName() . ' - ' . CalendarColorsData::getTitleByType($dost->getZahtev()),
          "start" => $dost->getDatum()->format('Y-m-d'),
          "datum" => $dost->getDatum()->format('d.m.Y'),
          "color" => CalendarColorsData::getColorByType($dost->getZahtev()),
          "name" => $dost->getUser()->getFullName(),
          "id" => $dost->getUser()->getId(),
          "zahtev" => $dost->getZahtev(),
          "razlog" => CalendarColorsData::getTitleByType($dost->getZahtev())
        ];
      }
    }

    return $dostupnost;
  }

  public function getAllDostupnostiDanas(): array {
    $company = $this->security->getUser()->getCompany();

    $danasnjiPlan = new DateTimeImmutable();

    $datum = date("d.m.Y");
    $danas = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $start = $danas->setTime(0,0);
    $stop = $danas->setTime(23,59);

    $noNedostupni = 0;
    $noVanZadatka = 0;
    $noKancelarija = 0;
    $noUnknown = 0;

    $nedostupni = [];
    $vanZadatka = [];
    $kancelarija = [];
    $unknown = [];

    $zamene = [];
    $plan = $this->getEntityManager()->getRepository(FastTask::class)->findOneBy(['company' => $company, 'datum' => $danasnjiPlan->setTime(14, 30)]);
    if (!is_null($plan)) {
      $zamene[] = $plan->getZgeo1();
      $zamene[] = $plan->getZgeo2();
      $zamene[] = $plan->getZgeo3();
      $zamene[] = $plan->getZgeo4();
      $zamene[] = $plan->getZgeo5();
      $zamene[] = $plan->getZgeo6();
      $zamene[] = $plan->getZgeo7();
      $zamene[] = $plan->getZgeo8();
      $zamene[] = $plan->getZgeo9();
      $zamene[] = $plan->getZgeo10();
    }

    $zamene = array_filter($zamene, function($value) {
      return $value !== null;
    });
    $zamene = array_unique($zamene);
    $zamene = array_values($zamene);

    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company], ['prezime' => 'ASC']);

    foreach ($users as $user) {
      if (!$this->checkDostupnost($user, $datum)) {
        $noNedostupni++;
        $nedostupni[] = $user;
      } else {
        if (!$user->isInTask()) {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          if (empty($tasks)) {
            if ($user->getProjectType() == TipProjektaData::LETECE) {
              if (!in_array($user->getId(), $zamene, true)) {
                $noUnknown++;
                $unknown[] = $user;
              }
            }
          } else {
            $noVanZadatka++;
            $vanZadatka[] = $user;
          }
        } else {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          foreach ($tasks as $tsk) {
            if (($tsk['status'] == TaskStatusData::ZAPOCETO && $tsk['task']->getCategory()->getId() == 6) || $tsk['task']->getProject()->getId() == 39) {
              $noKancelarija++;
              $kancelarija[] = $user;
            }
          }
        }

      }
    }

    return [
      'noNedostupni' => $noNedostupni,
      'noKancelarija' => $noKancelarija,
      'noVanZadatka' => $noVanZadatka,
      'noUnknown' => $noUnknown,
      'nedostupni' => $nedostupni,
      'kancelarija' => $kancelarija,
      'vanZadatka' => $vanZadatka,
      'unknown' => $unknown,
    ];


  }

  public function getAllDostupnostiSutra(): array {
    $company = $this->security->getUser()->getCompany();

    $sutrasnjiPlan = new DateTimeImmutable();

    $datum = date("d.m.Y");
    $danas = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $sutra = $danas->add(new DateInterval('P1D'));
    $datumSutra = $sutra->format('d.m.Y');
    $start = $sutra->setTime(0,0);
    $stop = $sutra->setTime(23,59);

    $noNedostupni = 0;
    $noVanZadatka = 0;
    $noKancelarija = 0;
    $noUnknown = 0;

    $nedostupni = [];
    $vanZadatka = [];
    $kancelarija = [];
    $unknown = [];

    $zamene = [];
    $plan = $this->getEntityManager()->getRepository(FastTask::class)->findOneBy(['company' => $company, 'datum' => $sutrasnjiPlan->modify('+1 day')->setTime(14, 30)]);
    if (!is_null($plan)) {
      $zamene[] = $plan->getZgeo1();
      $zamene[] = $plan->getZgeo2();
      $zamene[] = $plan->getZgeo3();
      $zamene[] = $plan->getZgeo4();
      $zamene[] = $plan->getZgeo5();
      $zamene[] = $plan->getZgeo6();
      $zamene[] = $plan->getZgeo7();
      $zamene[] = $plan->getZgeo8();
      $zamene[] = $plan->getZgeo9();
      $zamene[] = $plan->getZgeo10();
    }

    $zamene = array_filter($zamene, function($value) {
      return $value !== null;
    });
    $zamene = array_unique($zamene);
    $zamene = array_values($zamene);

    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company], ['prezime' => 'ASC']);

    foreach ($users as $user) {
      if (!$this->checkDostupnost($user, $datumSutra)) {
        $noNedostupni++;
        $nedostupni[] = $user;
      } else {
        if (!$user->isInTask()) {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          if (empty($tasks)) {
            if ($user->getProjectType() == TipProjektaData::LETECE) {
              if (!in_array($user->getId(), $zamene, true)) {
                $noUnknown++;
                $unknown[] = $user;
              }
            }
          } else {
            $noVanZadatka++;
            $vanZadatka[] = $user;
          }
        } else {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          foreach ($tasks as $tsk) {
            if (($tsk['status'] == TaskStatusData::ZAPOCETO && $tsk['task']->getCategory()->getId() == 6) || $tsk['task']->getProject()->getId() == 39) {
              $noKancelarija++;
              $kancelarija[] = $user;
            }
          }
        }

      }
    }

    return [
      'noNedostupni' => $noNedostupni,
      'noKancelarija' => $noKancelarija,
      'noVanZadatka' => $noVanZadatka,
      'noUnknown' => $noUnknown,
      'nedostupni' => $nedostupni,
      'kancelarija' => $kancelarija,
      'vanZadatka' => $vanZadatka,
      'unknown' => $unknown,
    ];


  }

  public function getAllNerasporedjenost(): array {
    $company = $this->security->getUser()->getCompany();
    $danas = new DateTimeImmutable();
    $sledeciDan = $danas->modify('+1 day')->setTime(14, 30);

    $users = [];

    $plan = $this->getEntityManager()->getRepository(FastTask::class)->findOneBy(['company' => $company, 'datum' => $sledeciDan]);

    if (!is_null($plan)) {

        $users[] = $plan->getGeo11();
        $users[] = $plan->getGeo21();
        $users[] = $plan->getGeo31();

        $users[] = $plan->getGeo12();
        $users[] = $plan->getGeo22();
        $users[] = $plan->getGeo32();

        $users[] = $plan->getGeo13();
        $users[] = $plan->getGeo23();
        $users[] = $plan->getGeo33();

        $users[] = $plan->getGeo14();
        $users[] = $plan->getGeo24();
        $users[] = $plan->getGeo34();

        $users[] = $plan->getGeo15();
        $users[] = $plan->getGeo25();
        $users[] = $plan->getGeo35();

        $users[] = $plan->getGeo16();
        $users[] = $plan->getGeo26();
        $users[] = $plan->getGeo36();

        $users[] = $plan->getGeo17();
        $users[] = $plan->getGeo27();
        $users[] = $plan->getGeo37();

        $users[] = $plan->getGeo18();
        $users[] = $plan->getGeo28();
        $users[] = $plan->getGeo38();

        $users[] = $plan->getGeo19();
        $users[] = $plan->getGeo29();
        $users[] = $plan->getGeo39();

        $users[] = $plan->getGeo110();
        $users[] = $plan->getGeo210();
        $users[] = $plan->getGeo310();


        $users[] = $plan->getZgeo1();
        $users[] = $plan->getZgeo2();
        $users[] = $plan->getZgeo3();
        $users[] = $plan->getZgeo4();
        $users[] = $plan->getZgeo5();
        $users[] = $plan->getZgeo6();
        $users[] = $plan->getZgeo7();
        $users[] = $plan->getZgeo8();
        $users[] = $plan->getZgeo9();
        $users[] = $plan->getZgeo10();




        $users = array_filter($users, function($value) {
          return $value !== null;
        });

    // Ukloni duplikate
        $users = array_unique($users);

    // Reindeksiraj niz ako je potrebno
        $users = array_values($users);

        $sviKorisnici =  $this->getEntityManager()->getRepository(User::class)->getRazlikaUsers($company, $users);
        $checkKoris = [];
        foreach ($sviKorisnici as $koris) {
          if ($this->checkDostupnost($koris, $danas->modify('+1 day')->format('d.m.Y'))) {
            $checkKoris[] = $koris;
          }
        }
      return
        [
          'brojNerasporedjenih' => count($checkKoris),
          'brojDostupnih' => count($checkKoris) + count($users),
          'nerasporedjeniSutra' => $checkKoris
        ];
    }

    return [];

  }

  public function checkDostupnost(User $user, string $datum): bool {

    $datum = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnost = $this->createQueryBuilder('t')
      ->where('t.type < 2')
      ->andWhere('t.datum = :danas')
      ->andWhere('t.User = :user')
      ->setParameter(':danas', $danas)
      ->setParameter(':user', $user->getId())
      ->getQuery()
      ->getResult();

    if (empty($dostupnost)) {
      return true;
    }

    return false;
  }

  public function checkIzlazak(User $user, string $datum): ?Availability {

    $datum = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $danas = $datum->format('Y-m-d 00:00:00');
    return $this->createQueryBuilder('t')
      ->where('t.type = 2')
      ->andWhere('t.datum = :danas')
      ->andWhere('t.User = :user')
      ->setParameter(':danas', $danas)
      ->setParameter(':user', $user->getId())
      ->getQuery()
      ->getOneOrNullResult();

  }

  public function addDostupnostDelete(StopwatchTime $stopwatchTime): ?Availability {
    $datum = $stopwatchTime->getStart();

    $dostupnost = new Availability();
    $dostupnost->setType(AvailabilityData::NEDOSTUPAN);
    $dostupnost->setDatum($datum->setTime(0, 0));
    $dostupnost->setUser($stopwatchTime->getTaskLog()->getUser());
    $dostupnost->setTask($stopwatchTime->getTaskLog()->getTask()->getId());

    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana

    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.datum BETWEEN :startDate AND :endDate')
      ->andWhere('t.User = :user')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('user', $stopwatchTime->getTaskLog()->getUser())
      ->getQuery()
      ->getResult();

    if (empty($dostupnosti)) {
      $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
    } else {
      foreach ($dostupnosti as $dost) {
        dd($dost);
        if ($dost->getType() == AvailabilityData::PRISUTAN) {
          $this->getEntityManager()->getRepository(Availability::class)->remove($dost);
          $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
        }
      }
    }
    return $dostupnost;
  }

  public function remove(Availability $entity): void {
    $this->getEntityManager()->remove($entity);

    $this->getEntityManager()->flush();

  }

//    /**
//     * @return Availability[] Returns an array of Availability objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Availability
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
