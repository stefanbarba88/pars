<?php

namespace App\Repository;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\FastTaskData;
use App\Classes\Data\UserRolesData;
use App\Entity\Availability;
use App\Entity\Calendar;
use App\Entity\Holiday;
use App\Entity\User;
use App\Service\MailService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Calendar>
 *
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarRepository extends ServiceEntityRepository {
  private Security $security;
  private $mail;
  public function __construct(ManagerRegistry $registry,MailService $mail, Security $security) {
    parent::__construct($registry, Calendar::class);
    $this->mail = $mail;
    $this->security = $security;
  }

  public function save(Calendar $calendar): Calendar {

    if (is_null($calendar->getId())) {
      $this->getEntityManager()->persist($calendar);
    }

    $this->getEntityManager()->flush();
    return $calendar;
  }

  public function countCalendarRequests(): int{
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('c');

    $qb->select($qb->expr()->count('c'))
      ->andWhere('c.status = :status')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', 1);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countCalendarRequestsCommand($company): int{

    $qb = $this->createQueryBuilder('c');

    $qb->select($qb->expr()->count('c'))
      ->andWhere('c.status = :status')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':status', 1);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function getDisabledDates(): array {
    $company = $this->security->getUser()->getCompany();
    $dates = [];
    $qb = $this->createQueryBuilder('f');
    $qb
      ->andWhere($qb->expr()->orX(
        $qb->expr()->eq('f.status', ':status2'),
        $qb->expr()->eq('f.status', ':status3'),
        $qb->expr()->eq('f.status', ':status4')
      ))
      ->andWhere('f.company = :company')
      ->setParameter('company', $company)
      ->setParameter('status2', FastTaskData::OPEN)
      ->setParameter('status3', FastTaskData::SAVED)
      ->setParameter('status4', FastTaskData::EDIT);


    $query = $qb->getQuery();
    $fastTasks = $query->getResult();

    if (!empty($fastTasks)) {
      foreach ($fastTasks as $task) {
        $dates[] = $task->getDatum()->format('d.m.Y');
      }
    }
    return $dates;
  }
  public function getRequestByUser(User $user, $year): array {
    $startDate = new DateTimeImmutable("$year-01-01");

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;

    $requests = $user->getCalendars()->toArray();

    foreach ($requests as $req) {
      if($req->getStart() >= $startDate) {
        if ($req->getStatus() != 0) {
          if ($req->getType() == CalendarColorsData::DAN) {
            $dan++;
          }
          if ($req->getType() == CalendarColorsData::ODMOR) {
            $odmor++;
          }
          if ($req->getType() == CalendarColorsData::BOLOVANJE) {
            $bolovanje++;
          }
          if ($req->getType() == CalendarColorsData::SLAVA) {
            $slava++;
          }

        }
      }
    }

    return  [
      'dan' => $dan,
      'odmor' => $odmor,
      'bolovanje' => $bolovanje,
      'slava' => $slava,
      'ukupno' => $dan + $odmor + $bolovanje + $slava
    ];

  }

//  public function finish(Calendar $calendar): Calendar {
//    $calendar->setStatus(2);
//
//    $this->save($calendar);
//    return $calendar;
//  }

  public function remove(Calendar $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Calendar {
    if (empty($id)) {
      $cal = new Calendar();
      $cal->setCompany($this->security->getUser()->getCompany());
      return $cal;
    }
    return $this->getEntityManager()->getRepository(Calendar::class)->find($id);
  }


  #[NoReturn] public function allowCalendar(Calendar $calendar): void {

    $calendar->setStatus(2);
    $company = $calendar->getCompany();
    $start = $calendar->getStart();
    $finish = $calendar->getFinish();
    $datumi = [];
    $current = clone $start;

    while ($current <= $finish) {
      $datumi[] = clone $current;
      $current = $current->add(new DateInterval('P1D'));
    }


    $radniDaniFirma = [];
    $neradniDaniZaposleni = [];

    if (!is_null($calendar->getCompany()->getSettings()->getWorkWeek()) || !empty($calendar->getCompany()->getSettings()->getWorkWeek())) {
      $radniDaniFirma = $calendar->getCompany()->getSettings()->getWorkWeek();
    }

    if (!is_null($calendar->getUser()->first()->getNeradniDan()) || !empty($calendar->getUser()->first()->getNeradniDan())) {
      $neradniDaniZaposleni = $calendar->getUser()->first()->getNeradniDan();
    }


    $brojDana = 0;

    foreach ($datumi as $datum) {
      if (!in_array($datum->format('N'), $neradniDaniZaposleni) && in_array($datum->format('N'), $radniDaniFirma)) {

        $check = $this->getEntityManager()->getRepository(Availability::class)->findBy(['User' => $calendar->getUser()->first(), 'datum' => $datum]);
        if (empty($check)) {
          $checkPraznik = $this->getEntityManager()->getRepository(Holiday::class)->findBy(['company' => $company, 'datum' => $datum]);
          if (empty($checkPraznik)) {
            $dostupnost = new Availability();
            $dostupnost->setDatum($datum);
            $dostupnost->setUser($calendar->getUser()->first());
            $dostupnost->setZahtev($calendar->getType());

            $dostupnost->setType(AvailabilityData::NEDOSTUPAN);

            if ($calendar->getPart() == 1) {
              $dostupnost->setType(AvailabilityData::IZASAO);
              $dostupnost->setVreme($calendar->getVreme());
            }
            $dostupnost->setCalendar($calendar->getId());
            $dostupnost->setCompany($calendar->getCompany());
            $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
          }
        }

      }
    }

    if ($brojDana == 0) {
      $this->getEntityManager()->getRepository(Calendar::class)->remove($calendar, true);
    } else {
      $this->mail->responseCalendar($calendar);
      $this->getEntityManager()->getRepository(Calendar::class)->save($calendar);
    }

  }

  public function getCalendarPaginator(User $loggedUser) {

//    $calendars = match ($loggedUser->getUserType()) {
//      UserRolesData::ROLE_EMPLOYEE => $this->createQueryBuilder('c')
//        ->andWhere('c.user <> :user')
//        ->setParameter(':user', $loggedUser)
//        ->addOrderBy('c.start', 'DESC')
//        ->getQuery(),
//
//      default => $this->createQueryBuilder('c')
//        ->addOrderBy('c.start', 'DESC')
//        ->getQuery(),
//
//    };

//    return $this->createQueryBuilder('c')
//      ->orderBy('CASE WHEN c.status = 1 THEN 0 WHEN c.status = 2 THEN 1 WHEN c.status = 3 THEN 2 ELSE 3 END', 'ASC')
//      ->addOrderBy('c.start', 'DESC')
//      ->getQuery();
    $company = $loggedUser->getCompany();
    return $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->addOrderBy('c.start', 'DESC')
      ->getQuery();

  }

  public function getCalendarConfirmCommand($company) {

    $today = new \DateTimeImmutable();
    $startDate = $today->format('Y-m-d 00:00:00'); // Početak dana
    $stopDate = $today->format('Y-m-d 23:59:59'); // Kraj dana

    return $this->createQueryBuilder('c')
      ->where('c.start BETWEEN :start AND :end')
      ->andWhere('c.company = :company')
      ->setParameter('start', $startDate)
      ->setParameter('end', $stopDate)
      ->andWhere('c.status = 1')
      ->setParameter(':company', $company)
      ->addOrderBy('c.start', 'DESC')
      ->getQuery()
      ->getResult();



  }

//    /**
//     * @return Calendar[] Returns an array of Calendar objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Calendar
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
