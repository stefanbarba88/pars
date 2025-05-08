<?php

namespace App\Repository;

use App\Classes\Data\CalendarColorsData;
use App\Classes\Data\CalendarData;
use App\Classes\Data\UserRolesData;
use App\Entity\Calendar;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
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
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Calendar::class);
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

  public function getRequestByUserMesec(User $user, DateTimeImmutable $date): array {
//    $startDate = $date->modify('first day of this month')->setTime(0, 0);
//    $endDate = $date->modify('last day of this month')->setTime(23, 59);

    $startDate = $date->modify('first day of last month')->setTime(0, 0);
    $endDate = $date->modify('last day of last month')->setTime(23, 59);

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;

    $requests = $user->getCalendars()->toArray();

    foreach ($requests as $req) {
      if($req->getStart() >= $startDate && $req->getStart() <= $endDate) {
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
    $results = $this->createQueryBuilder('c')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->getQuery()
      ->getResult();

// Ručno sortiranje u PHP-u
    usort($results, function($a, $b) {
      $statusOrder = [1, 2, 3, 0];

      $statusA = array_search($a->getStatus(), $statusOrder);
      $statusB = array_search($b->getStatus(), $statusOrder);

      if ($statusA === $statusB) {
        // Sort by start date DESC
        return $b->getStart() <=> $a->getStart();
      }

      // Sort by status first
      return $statusA <=> $statusB;
    });

    return $results;

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
