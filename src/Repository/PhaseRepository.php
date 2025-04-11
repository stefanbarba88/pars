<?php

namespace App\Repository;

use App\Classes\Data\TaskStatusData;
use App\Entity\ManagerChecklist;
use App\Entity\Phase;
use App\Entity\Project;
use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Phase>
 *
 * @method Phase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phase[]    findAll()
 * @method Phase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhaseRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Phase::class);
  }

  public function save(Phase $entity): Phase {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();
    return $entity;
  }

  public function remove(Phase $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForFormProject(Project $project = null): Phase {

    $phase = new Phase();
    $phase->setProject($project);
    return $phase;

  }

  public function getDaysRemainingPhase(Phase $phase): array {
    $poruka = '';
    $klasa = '';
    $klasa1 = '';
    $now = new DateTimeImmutable();
    $now->setTime(0,0);

    if (!is_null($phase->getDeadline())) {
      $contractEndDate = $phase->getDeadline();
      // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
      $days = (int) $now->diff($contractEndDate)->format('%R%a');

      if ($days > 0 && $days < 7) {
        $poruka = 'Rok za završetak faze ističe za ' . $days . ' dana.';
        $klasa = 'bg-info bg-opacity-50';
      } elseif ($days == 0) {
        $poruka = 'Rok za završetak faze ističe danas.';
        $klasa = 'bg-warning bg-opacity-50';
        $klasa1 = 'bg-warning bg-opacity-10';
      } elseif ($days < 0) {
        $poruka = 'Rok za završetak faze je istekao pre ' . abs($days) . ' dana.';
        $klasa = 'bg-danger bg-opacity-50';
        $klasa1 = 'bg-danger bg-opacity-10';
      }
    }

    return [
      'klasa' => $klasa,
      'poruka' => $poruka,
      'klasa1' => $klasa1
    ];
  }

    //poziva se nakon svakog cuvanja ili brisanja zadtaka ili internog zadatka
  public function calculatePhaseEfficiency(Phase $phase): void {

    $regularTasks = $this->getEntityManager()->getRepository(Task::class)->countTasksPhase($phase);
    $internalTasks = $this->getEntityManager()->getRepository(ManagerChecklist::class)->countTasksPhase($phase);
    $totalTasks = $regularTasks[0] + $internalTasks[0];
    $completedTasks = $regularTasks[1] + $internalTasks[1];

    if ($totalTasks === 0) {
      $efficiency = 0.00;
    } else {
      $efficiency = ($completedTasks / $totalTasks) * 100;
    }

    if ($efficiency == 100) {
      $phase->setStatus(TaskStatusData::ZAVRSENO);
    } elseif ($efficiency > 0 && $efficiency < 100) {
      $phase->setStatus(TaskStatusData::ZAPOCETO);
    } else {
      $phase->setStatus(TaskStatusData::NIJE_ZAPOCETO);
    }

    $phase->setPercent($efficiency);
    $this->save($phase);

  }

  public function calculateProjectEfficiency(Project $project): float {
    $phases = $project->getPhases(); // pretpostavljam da to postoji

    if (count($phases) === 0) {
      return 0.00;
    }

    $totalProgress = 0;

    foreach ($phases as $phase) {
      $regularTasks = $this->getEntityManager()->getRepository(Task::class)->countTasksPhase($phase);
      $internalTasks = $this->getEntityManager()->getRepository(ManagerChecklist::class)->countTasksPhase($phase);

      $totalTasks = $regularTasks[0] + $internalTasks[0];
      $completedTasks = $regularTasks[1] + $internalTasks[1];

      if ($totalTasks === 0) {
        $efficiency = 0.00;
      } else {
        $efficiency = ($completedTasks / $totalTasks) * 100;
      }

      $totalProgress += $efficiency;
    }

    return round($totalProgress / count($phases), 2);
  }


//    /**
//     * @return Phase[] Returns an array of Phase objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Phase
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
