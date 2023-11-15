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

/**
 * @extends ServiceEntityRepository<Calendar>
 *
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Calendar::class);
  }

  public function save(Calendar $calendar): Calendar {

    if (is_null($calendar->getId())) {
      $this->getEntityManager()->persist($calendar);
    }

    $this->getEntityManager()->flush();
    return $calendar;
  }

  public function getRequestByUser(User $user): array {

    $dan = 0;
    $odmor = 0;
    $slava = 0;
    $bolovanje = 0;
    $requests = $user->getCalendars()->toArray();

    foreach ($requests as $req) {
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
      return new Calendar();
    }
    return $this->getEntityManager()->getRepository(Calendar::class)->find($id);
  }

  public function getCalendarPaginator(User $loggedUser) {

    $calendars = match ($loggedUser->getUserType()) {
      UserRolesData::ROLE_EMPLOYEE => $this->createQueryBuilder('c')
        ->andWhere('c.user <> :user')
        ->setParameter(':user', $loggedUser)
        ->addOrderBy('c.start', 'DESC')
        ->getQuery(),

      default => $this->createQueryBuilder('c')
        ->addOrderBy('c.start', 'DESC')
        ->getQuery(),

    };

    return $calendars;

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
