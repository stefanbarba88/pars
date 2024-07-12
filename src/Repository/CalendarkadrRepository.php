<?php

namespace App\Repository;

use App\Classes\Data\CalendarData;
use App\Entity\Calendarkadr;
use App\Entity\Company;
use App\Entity\User;
use App\Entity\Vacation;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Calendarkadr>
 *
 * @method Calendarkadr|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendarkadr|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendarkadr[]    findAll()
 * @method Calendarkadr[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarkadrRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Calendarkadr::class);
    $this->security = $security;
  }

  public function save(Calendarkadr $calendarkadr): Calendarkadr {
    $vacation = $calendarkadr->getUser()->getVacation();

    if (is_null($calendarkadr->getId())) {
      if ($calendarkadr->getType() == CalendarData::STARI_ODMOR) {
        $vacation->setOldUsed($vacation->getOldUsed() + $calendarkadr->getDays());
      } elseif ($calendarkadr->getType() == CalendarData::ODMOR) {
        $vacation->setUsed1($vacation->getUsed1() + $calendarkadr->getDays());
      } elseif ($calendarkadr->getType() == CalendarData::SLAVA) {
        $vacation->setSlava($vacation->getSlava() + $calendarkadr->getDays());
      } else {
        $vacation->setOther1($vacation->getOther1() + $calendarkadr->getDays());
      }
      $this->getEntityManager()->persist($calendarkadr);
    }

    if ($calendarkadr->getStatus() == 0) {
      if ($calendarkadr->getType() == CalendarData::STARI_ODMOR) {
        $vacation->setOldUsed($vacation->getOldUsed() - $calendarkadr->getDays());
      } elseif ($calendarkadr->getType() == CalendarData::ODMOR) {
        $vacation->setUsed1($vacation->getUsed1() - $calendarkadr->getDays());
      } elseif ($calendarkadr->getType() == CalendarData::SLAVA) {
        $vacation->setSlava($vacation->getSlava() - $calendarkadr->getDays());
      } else {
        $vacation->setOther1($vacation->getOther1() - $calendarkadr->getDays());
      }
    }

    $this->getEntityManager()->getRepository(Vacation::class)->save($vacation);
    $this->getEntityManager()->flush();
    return $calendarkadr;
  }

  public function remove(Calendarkadr $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
  public function findForForm(int $user, int $id = 0): Calendarkadr {

    if (empty($id)) {
      $zaposleni = $this->getEntityManager()->getRepository(User::class)->find($user);
      $calendarkadr = new Calendarkadr();
      $calendarkadr->setCompany($zaposleni->getCompany());
      $calendarkadr->setUser($zaposleni);
      return $calendarkadr;
    }
    return $this->getEntityManager()->getRepository(Calendarkadr::class)->find($id);
  }

  public function getCalendarkadrPaginator(User $user, $filter): QueryBuilder {

    $qb = $this->createQueryBuilder('c');

    $qb->where('c.user = :user')
      ->andWhere('c.status = 1')
      ->setParameter(':user', $user);

    if (!empty($filter['aneks'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.aneks', ':aneks'),
      ))
        ->setParameter('aneks', '%' . $filter['aneks'] . '%');
    }

    $qb
      ->orderBy('c.start', 'DESC')
      ->getQuery();
    return $qb;

  }

  public function getAneksByCompany(Company $company, $filterBy){

    $today = new DateTimeImmutable(); // Dohvati trenutni datum i vrijeme

    $qb = $this->createQueryBuilder('c');

//    if (!empty($filterBy['period'])) {
//
//      $dates = explode(' - ', $filterBy['period']);
//
//      $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
//      $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);
//      $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
//      $stopDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana
//
//      $qb->where($qb->expr()->between('c.start', ':start', ':end'));
//      $qb->setParameter('start', $startDate);
//      $qb->setParameter('end', $stopDate);
//
//    }

    $qb->andWhere('c.company = :company');
    $qb->setParameter(':company', $company);

    if (!empty($filter['aneks'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('c.aneks', ':aneks'),
      ))
        ->setParameter('aneks', '%' . $filter['aneks'] . '%');
    }
    if (!empty($filterBy['user'])) {
      $user = $this->getEntityManager()->getRepository(User::class)->find($filterBy['user']);
      $qb->andWhere('c.user = :user');
      $qb->setParameter('user', $user);
    }


    $qb->orderBy('c.status', 'DESC')
      ->getQuery();

//    foreach ($tasks as $task) {
//      $status = $this->taskStatus($task);
//
//      if ($status == TaskStatusData::ZAVRSENO ) {
//        $list[] = [
//          'task' => $task,
//          'status' => $status,
//          'logStatus' => $this->getEntityManager()->getRepository(TaskLog::class)->getLogStatus($task)
//        ];
//      }
//    }
//    usort($list, function ($a, $b) {
//      return $a['status'] <=> $b['status'];
//    });

    return $qb;
  }

//    /**
//     * @return Calendarkadr[] Returns an array of Calendarkadr objects
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

//    public function findOneBySomeField($value): ?Calendarkadr
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
