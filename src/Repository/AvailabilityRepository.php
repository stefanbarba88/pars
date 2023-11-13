<?php

namespace App\Repository;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Array_;

/**
 * @extends ServiceEntityRepository<Availability>
 *
 * @method Availability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Availability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Availability[]    findAll()
 * @method Availability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvailabilityRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Availability::class);
  }

  public function save(Availability $availability): Availability {

    if (is_null($availability->getId())) {
      $this->getEntityManager()->persist($availability);
    }

    $this->getEntityManager()->flush();
    return $availability;
  }

  public function getDaysByUser(User $user): array {

    $nedostupan = 0;
    $izasao = 0;
    $dostupan = 0;

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;
    $ostalo = 0;

    $requests = $this->getEntityManager()->getRepository(Availability::class)->findBy(['User' => $user]);

    foreach ($requests as $req) {
        if ($req->getType() == AvailabilityData::PRISUTAN) {
          $dostupan++;
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
        }
    }

    return  [
      'dostupan' => $dostupan,
      'izasao' => $izasao,
      'nedostupan' => $nedostupan,
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ostalo' => $ostalo,
      'ukupno' => $dan + $odmor + $bolovanje + $slava
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
    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type <> 3')
      ->andWhere('t.datum >= :danas')
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
          "razlog" => CalendarColorsData::getTitleByType($dost->getZahtev()),
          "text" => CalendarColorsData::getTextByType($dost->getZahtev())
        ];
      }
    }

    return $dostupnost;
  }

  public function getDostupnostByUser(User $user): array {
    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
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

    return $dostupnost;
  }

  public function getDostupnostDanas(): array {
    $dostupnost = [];
    $datum = new DateTimeImmutable();
    $danas = $datum->format('Y-m-d 00:00:00');
    $dostupnosti = $this->createQueryBuilder('t')
      ->where('t.type <> 3')
      ->andWhere('t.datum = :danas')
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

    $datum = date("d.m.Y");
    $danas = DateTimeImmutable::createFromFormat('d.m.Y', $datum);
    $start = $danas->setTime(0,0);
    $stop = $danas->setTime(23,59);
    $noNedostupni = 0;
    $noVanZadatka = 0;
    $noKancelarija = 0;

    $nedostupni = [];
    $vanZadatka = [];
    $kancelarija = [];

    $users = $this->getEntityManager()->getRepository(User::class)->findBy(['userType' => UserRolesData::ROLE_EMPLOYEE, 'isSuspended' => false], ['prezime' => 'ASC']);

    foreach ($users as $user) {
      if (!$this->checkDostupnost($user, $datum)) {
        $noNedostupni++;
        $nedostupni[] = $user;
      } else {


        if (!$user->isInTask()) {
          $noVanZadatka++;
          $vanZadatka[] = $user;
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
      'nedostupni' => $nedostupni,
      'kancelarija' => $kancelarija,
      'vanZadatka' => $vanZadatka,
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
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->getQuery()
      ->getResult();

    if (empty($dostupnosti)) {
      $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
    } else {
      foreach ($dostupnosti as $dost) {
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
