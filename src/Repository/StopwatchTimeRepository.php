<?php

namespace App\Repository;

use App\Entity\StopwatchTime;
use App\Entity\TaskLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

  public function remove(StopwatchTime $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getStopwatches(TaskLog $taskLog): array {
    $stopwatches = [];

    $times = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $taskLog]);

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
      ];
    }
    return $stopwatches;
  }

  public function setTime(StopwatchTime $stopwatch): StopwatchTime {

    $hours = $stopwatch->getStart()->diff($stopwatch->getStop())->h;
    $minutes = $stopwatch->getStart()->diff($stopwatch->getStop())->i;

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

    $stopwatch->setDiff($diff);
    $stopwatch->setDiffRounded($diffRound);



    return $this->save($stopwatch);



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
