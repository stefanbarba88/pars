<?php

namespace App\Repository;

use App\Classes\Data\PrioritetData;
use App\Classes\Data\TimerPriorityData;
use App\Classes\Data\UserRolesData;
use App\Entity\Image;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StopwatchTime>
 *
 * @method StopwatchTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method StopwatchTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method StopwatchTime[]    findAll()
 * @method StopwatchTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StopwatchTimeRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, StopwatchTime::class);
  }

  public function save(StopwatchTime $stopwatch): StopwatchTime {
    if (is_null($stopwatch->getId())) {
      $this->getEntityManager()->persist($stopwatch);
    }

    $this->getEntityManager()->flush();
    return $stopwatch;
  }

  public function close(StopwatchTime $stopwatch): StopwatchTime {

    $stopwatch->setStop(new DateTimeImmutable());
    $hours = $stopwatch->getStart()->diff($stopwatch->getStop())->h;
    $minutes = $stopwatch->getStart()->diff($stopwatch->getStop())->i;
    $this->setTime($stopwatch, $hours, $minutes);
    $stopwatch->setIsManuallyClosed(true);
    return $this->save($stopwatch);
  }

  public function remove(StopwatchTime $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getStopwatches(TaskLog $taskLog): array {
    $stopwatches = [];

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.diff is NOT NULL')
      ->orderBy('u.isDeleted', 'ASC')
      ->addOrderBy('u.isEdited', 'ASC')
      ->addOrderBy('u.id', 'DESC')
      ->getQuery()
      ->getResult();

    foreach ($times as $time) {
      $stopwatches [] = [
        'id' => $time->getId(),
        'hours' => intdiv($time->getDiffRounded(), 60),
        'minutes' => $time->getDiffRounded() % 60,
        'hoursReal' => intdiv($time->getDiff(), 60),
        'minutesReal' => $time->getDiff() % 60,
        'start' => $time->getStart(),
        'stop' => $time->getStop(),
        'startLon' => $time->getLon(),
        'startLat' => $time->getLat(),
        'stopLon' => $time->getLonStop(),
        'stopLat' => $time->getLatStop(),
        'description' => $time->getDescription(),
        'min' => $time->getMin(),
        'activity' => $time->getActivity(),
        'images' => $time->getImage(),
        'pdfs' => $time->getPdf(),
        'created' => $time->getCreated(),
        'edited' => $time->isIsEdited(),
        'editedBy' => $time->getEditedBy(),
        'deleted' => $time->isIsDeleted(),
        'deletedBy' => $time->getDeletedBy(),
        'manually' => $time->isIsManuallyClosed(),
      ];
    }
    return $stopwatches;
  }

  public function getStopwatchesActive(TaskLog $taskLog): array {
    $stopwatches = [];

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.diff is NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->getQuery()
      ->getResult();

    foreach ($times as $time) {
      $stopwatches [] = [
        'id' => $time->getId(),
        'hours' => intdiv($time->getDiffRounded(), 60),
        'minutes' => $time->getDiffRounded() % 60,
        'hoursReal' => intdiv($time->getDiff(), 60),
        'minutesReal' => $time->getDiff() % 60,
        'start' => $time->getStart(),
        'stop' => $time->getStop(),
        'startLon' => $time->getLon(),
        'startLat' => $time->getLat(),
        'stopLon' => $time->getLonStop(),
        'stopLat' => $time->getLatStop(),
        'description' => $time->getDescription(),
        'min' => $time->getMin(),
        'activity' => $time->getActivity(),
        'images' => $time->getImage(),
        'pdfs' => $time->getPdf(),
        'created' => $time->getCreated(),
        'edited' => $time->isIsEdited(),
        'editedBy' => $time->getEditedBy(),
        'deleted' => $time->isIsDeleted(),
        'deletedBy' => $time->getDeletedBy(),
        'manually' => $time->isIsManuallyClosed(),
      ];
    }
    return $stopwatches;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function countStopwatches($taskLog): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.isDeleted = 0')
      ->andWhere('u.diff is NOT NULL');

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }
  public function getStopwatchTime(TaskLog $taskLog): array {

    $hours = 0;
    $minutes = 0;
    $hoursReal = 0;
    $minutesReal = 0;

//    $times = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $taskLog]);
    $times = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $taskLog, 'isDeleted' => false]);

    foreach ($times as $time) {
        $hours = $hours + intdiv($time->getDiffRounded(), 60);
        $minutes = $minutes + $time->getDiffRounded() % 60;
        $hoursReal = $hoursReal + intdiv($time->getDiff(), 60);
        $minutesReal = $minutesReal + $time->getDiff() % 60;
    }

    $minutesU = $hours*60 + $minutes;
    $minutesRealU = $hoursReal*60 + $minutesReal;

    $h = intdiv($minutesU, 60);
    $m = $minutesU % 60;
    $hR = intdiv($minutesRealU, 60);
    $mR = $minutesRealU % 60;


    if ($h < 10) {
      $h = '0' . $h;
    }
    if ($m < 10) {
      $m = '0' . $m;
    }
    if ($hR < 10) {
      $hR = '0' . $hR;
    }
    if ($mR < 10) {
      $mR = '0' . $mR;
    }


    return [
      'hours' => $h,
      'minutes' => $m,
      'hoursR' => $hR,
      'minutesR' => $mR,
    ];

  }

  public function getStopwatchTimeByTask(Task $task): array {

    $priority = $task->getProject()->getTimerPriority();
    $priorityTitle = $task->getProject()->getTimerPriorityJson();

    $hoursTotalRounded = 0;
    $minutesTotalRounded = 0;
    $hoursTotalReal = 0;
    $minutesTotalReal = 0;

    $hoursRounded = 0;
    $minutesRounded = 0;
    $hoursReal = 0;
    $minutesReal = 0;

    $logs = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $task]);

    foreach ($logs as $log) {
      $times = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $log, 'isDeleted' => false]);
      foreach ($times as $time) {
        $hoursTotalRounded = $hoursTotalRounded + intdiv($time->getDiffRounded(), 60);
        $minutesTotalRounded = $minutesTotalRounded + $time->getDiffRounded() % 60;
        $hoursTotalReal = $hoursTotalReal + intdiv($time->getDiff(), 60);
        $minutesTotalReal = $minutesTotalReal + $time->getDiff() % 60;
      }

    }

    $minutesRoundedU = $hoursTotalRounded * 60 + $minutesTotalRounded;
    $minutesRealU = $hoursTotalReal * 60 + $minutesTotalReal;

    $h = intdiv($minutesRoundedU, 60);
    $m = $minutesRoundedU % 60;
    $hR = intdiv($minutesRealU, 60);
    $mR = $minutesRealU % 60;


    if ($priority == TimerPriorityData::FIRST_ASSIGN) {
      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneBy(['task' => $task], ['id' => 'ASC']);
    } elseif ($priority == TimerPriorityData::ROLE_GEO) {
      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 1);
    } elseif ($priority == TimerPriorityData::ROLE_FIG) {
      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 2);
    }

    if(!empty($logPriority)) {
      $timesPriority = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $logPriority, 'isDeleted' => false]);
      foreach ($timesPriority as $time) {
        $hoursRounded = $hoursRounded + intdiv($time->getDiffRounded(), 60);
        $minutesRounded = $minutesRounded + $time->getDiffRounded() % 60;
        $hoursReal = $hoursReal + intdiv($time->getDiff(), 60);
        $minutesReal = $minutesReal + $time->getDiff() % 60;
      }
      $minutes = $hoursRounded * 60 + $minutesRounded;
      $minutesR = $hoursReal * 60 + $minutesReal;

      $htP = intdiv($minutes, 60);
      $mtP = $minutes % 60;
      $hRtP = intdiv($minutesR, 60);
      $mRtP = $minutesR % 60;

      return [
        'hours' => $h,
        'minutes' => $m,
        'hoursReal' => $hR,
        'minutesReal' => $mR,
        'hoursTimePriority' => $htP,
        'minutesTimePriority' => $mtP,
        'hoursRealTimePriority' => $hRtP,
        'minutesRealTimePriority' => $mRtP,
        'priority' => $priorityTitle
      ];
    }

    return [
      'hours' => $h,
      'minutes' => $m,
      'hoursReal' => $hR,
      'minutesReal' => $mR,
      'hoursTimePriority' => $h,
      'minutesTimePriority' => $m,
      'hoursRealTimePriority' => $hR,
      'minutesRealTimePriority' => $mR,
      'priority' => $priorityTitle
    ];
  }

  public function lastEdit(TaskLog $taskLog): ?StopwatchTime {
    return $this->getEntityManager()->getRepository(StopwatchTime::class)->findOneBy(['taskLog' => $taskLog],['updated' => 'DESC']);
  }

//  public function findOneByUserPosition(TaskLog $taskLog, int $userPosition): ?StopwatchTime {
//
//      $stopwatches = [];
//
//    return $this->createQueryBuilder('s')
//      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
//      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
//      ->innerJoin(Image::class, 'i', Join::WITH, 's = i.stopwatchTime')
//      ->andWhere('t.id = :taskId')
//      ->andWhere('s.isDeleted = 0')
//      ->setParameter(':taskId', $task->getId())
//      ->getQuery()
//      ->getResult();
////      foreach ($times as $time) {
////        $stopwatches [] = [
////          'id' => $time->getId(),
////          'hours' => intdiv($time->getDiffRounded(), 60),
////          'minutes' => $time->getDiffRounded() % 60,
////          'hoursReal' => intdiv($time->getDiff(), 60),
////          'minutesReal' => $time->getDiff() % 60,
////          'start' => $time->getStart(),
////          'stop' => $time->getStop(),
////          'startLon' => $time->getLon(),
////          'startLat' => $time->getLat(),
////          'stopLon' => $time->getLonStop(),
////          'stopLat' => $time->getLatStop(),
////          'description' => $time->getDescription(),
////          'min' => $time->getMin(),
////          'activity' => $time->getActivity(),
////          'images' => $time->getImage(),
////          'pdfs' => $time->getPdf(),
////          'created' => $time->getCreated(),
////          'edited' => $time->isIsEdited(),
////          'editedBy' => $time->getEditedBy(),
////          'deleted' => $time->isIsDeleted(),
////          'deletedBy' => $time->getDeletedBy(),
////          'manually' => $time->isIsManuallyClosed(),
////        ];
////      }
//
//
//
//
//    return $this->getEntityManager()->getRepository(StopwatchTime::class)->findOneBy(['taskLog' => $taskLog],['updated' => 'DESC']);
//  }

  public function setTime(StopwatchTime $stopwatch, int $hours, int $minutes): StopwatchTime {

    $task = $stopwatch->getTaskLog()->getTask();
    $project = $task->getProject();

    $diff = ($hours * 60) + ($minutes);
//srediti ovaj kod
    if(!is_null($task->isIsTimeRoundUp())) {
      if($task->isIsTimeRoundUp()) {
        $min = $task->getMinEntry();
        $stopwatch->setMin($min);

        $roundInt = $task->getRoundingInterval();

        $minRound = (round($minutes / $roundInt) * $roundInt);
        $hourRound = $hours;

        if ($minRound == 60) {
          $minRound = 0;
          $hourRound++;
        }

        $diffRound = ($hourRound * 60) + ($minRound);

        if ($min > $diffRound) {
          $diffRound = $min;
        }

      } else {
        $diffRound = $diff;
      }
    } else {
      if($project->isTimeRoundUp()) {
        $min = $project->getMinEntry();
        $stopwatch->setMin($min);

        $roundInt = $project->getRoundingInterval();

        $minRound = (round($minutes / $roundInt) * $roundInt);
        $hourRound = $hours;

        if ($minRound == 60) {
          $minRound = 0;
          $hourRound++;
        }

        $diffRound = ($hourRound * 60) + ($minRound);

        if ($min > $diffRound) {
          $diffRound = $min;
        }
      } else {
        $diffRound = $diff;
      }
    }

    if ($diff == 0) {
      $diff = 1;
    }
    $stopwatch->setDiff($diff);
    $stopwatch->setDiffRounded($diffRound);

    return $stopwatch;
//    return $this->save($stopwatch);

  }

  public function deleteStopwatch(StopwatchTime $stopwatch, User $user): StopwatchTime {

    $stopwatch->setIsDeleted(true);
    $stopwatch->setIsEdited(true);
    $stopwatch->setDeletedBy($user);
    $stopwatch->setEditedBy($user);

    return $this->save($stopwatch);

  }

  public function findForForm(TaskLog $taskLog, int $id = 0): StopwatchTime {
    if (empty($id)) {
      $stopwatch = new StopwatchTime();
      $stopwatch->setTaskLog($taskLog);

      return $stopwatch;
    }
    return $this->getEntityManager()->getRepository(StopwatchTime::class)->find($id);

  }

//  public function setTime(StopwatchTime $stopwatch): StopwatchTime {
//
//  }

//    /**
//     * @return StopwatchTime[] Returns an array of StopwatchTime objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StopwatchTime
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
