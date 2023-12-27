<?php

namespace App\Repository;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
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
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ostalo' => $ostalo,
      'neradniPraznik' => $neradniPraznik,
      'neradniKolektivniOdmor' => $neradniKolektivniOdmor,
      'neradnaNedelja' => $neradnaNedelja,
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
      ->where('t.type <> 3')
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



    return array_merge($dostupnost, $dostupnost1);
  }

  public function getDostupnostPaginator() {
    $company = $this->security->getUser()->getCompany();
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type <> 3')
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
      ->where('t.type <> 3')
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
      ->where('t.type <> 3')
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

  public function getDostupnostByUserTwigSutra(User $user): ?int {

    $datum = new DateTimeImmutable();
    $sutra = $datum->add(new DateInterval('P1D'));
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type <> 3')
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
      ->where('t.type <> 3')
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

    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company], ['prezime' => 'ASC']);

    foreach ($users as $user) {
      if (!$this->checkDostupnost($user, $datum)) {
        $noNedostupni++;
        $nedostupni[] = $user;
      } else {
        if (!$user->isInTask()) {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          if (empty($tasks)) {
            $noUnknown++;
            $unknown[] = $user;
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

    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false, 'company' => $company], ['prezime' => 'ASC']);

    foreach ($users as $user) {
      if (!$this->checkDostupnost($user, $datumSutra)) {
        $noNedostupni++;
        $nedostupni[] = $user;
      } else {
        if (!$user->isInTask()) {
          $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
          if (empty($tasks)) {
            $noUnknown++;
            $unknown[] = $user;
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

  public function checkDostupnost(User $user, string $datum): bool {

    $datum = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnost = $this->createQueryBuilder('t')
      ->where('t.type <> 3')
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
