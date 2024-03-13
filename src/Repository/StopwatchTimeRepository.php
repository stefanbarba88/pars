<?php

namespace App\Repository;

use App\Classes\Data\AvailabilityData;
use App\Classes\Data\TipNeradnihDanaData;
use App\Entity\Availability;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Holiday;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<StopwatchTime>
 *
 * @method StopwatchTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method StopwatchTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method StopwatchTime[]    findAll()
 * @method StopwatchTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StopwatchTimeRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, StopwatchTime::class);
    $this->security = $security;
  }

  public function addDostupnost(User $user): ?Availability {

    $company = $user->getCompany();

    $datum = new DateTimeImmutable();

    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana

    $datumCheck = $datum->setTime(0,0);

    $merenja = $this->createQueryBuilder('t')
      ->where('t.start BETWEEN :startDate AND :endDate')
      ->andWhere('t.isDeleted = 0')
      ->andWhere('t.company = :company')
      ->setParameter('company', $company)
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->getQuery()
      ->getResult();



    $merenjeUser = [];
    foreach ($merenja as $merenje) {
      if ($merenje->getTaskLog()->getUser() == $user) {
        $merenjeUser[] = $merenje;
      }
    }

    $dostupnost = new Availability();
    if (!empty($merenjeUser)) {
      $dostupnost->setType(AvailabilityData::PRISUTAN);
    } else {
      $dostupnost->setType(AvailabilityData::NEDOSTUPAN);
    }
    $dostupnost->setDatum($datum->setTime(0, 0));
    $dostupnost->setUser($user);
    $dostupnost->setCompany($company);

    $dostupnost->setTypeDay(0);

    $praznik = $this->getEntityManager()->getRepository(Holiday::class)->findOneBy(['datum' => $datumCheck, 'company' => $company, 'isSuspended' => false]);

    $workWeek = $company->getWorkWeek();

    if ($datum->format('N') >= $workWeek) {
      $dostupnost->setTypeDay(TipNeradnihDanaData::NEDELJA);
      if (!is_null($praznik)) {
        if ($praznik->getType() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $dostupnost->setTypeDay(TipNeradnihDanaData::NEDELJA_ODMOR);
          if ($praznik->getType() == TipNeradnihDanaData::PRAZNIK) {
            $dostupnost->setTypeDay(TipNeradnihDanaData::NEDELJA_PRAZNIK);
          }
        }
      }
    } else {
      if (!is_null($praznik)) {
        if ($praznik->getType() == TipNeradnihDanaData::KOLEKTIVNI_ODMOR) {
          $dostupnost->setTypeDay(TipNeradnihDanaData::KOLEKTIVNI_ODMOR);
          if ($praznik->getType() == TipNeradnihDanaData::PRAZNIK) {
            $dostupnost->setTypeDay(TipNeradnihDanaData::PRAZNIK);
          }
        }
      }
    }

    if (!is_null($praznik)) {
      if ($praznik->getType() == TipNeradnihDanaData::PRAZNIK || $praznik->getType() == TipNeradnihDanaData::NEDELJA_PRAZNIK) {
        if (empty($merenjeUser)) {
          return $dostupnost;
        }
      }
    }

    $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
    return $dostupnost;
  }



  public function removeDostupnost(StopwatchTime $stopwatchTime): ?Availability {

    $datum = new DateTimeImmutable();

    $startDate = $datum->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $datum->format('Y-m-d 23:59:59'); // Kraj dana

    $merenja = $this->createQueryBuilder('t')
      ->where('t.start BETWEEN :startDate AND :endDate')
      ->andWhere('t.isDeleted = 0')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->getQuery()
      ->getResult();

    if (empty($merenja)) {
      $dostupnost = $this->getEntityManager()->getRepository(Availability::class)->findOneBy(['datum' => $datum->setTime(0, 0)]);
      $dostupnost->setType(AvailabilityData::PRISUTAN);
      $dostupnost->setDatum($datum);
      $dostupnost->setUser($stopwatchTime->getTaskLog()->getUser());
      $dostupnost->setTask($stopwatchTime->getTaskLog()->getTask()->getId());
      $this->getEntityManager()->getRepository(Availability::class)->save($dostupnost);
      return $dostupnost;
    }
    return null;
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

    $days = $stopwatch->getStart()->diff($stopwatch->getStop())->d;
    $hours = $stopwatch->getStart()->diff($stopwatch->getStop())->h;
    $hours = $days * 24 + $hours;
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


  public function checkAddStopwatch(string $datum, TaskLog $taskLog): bool {

    list($pocetak, $kraj) = explode(' - ', $datum);

    $format = 'd.m.Y H:i';

    $start = DateTimeImmutable::createFromFormat($format, $pocetak);
    $stop = DateTimeImmutable::createFromFormat($format, $kraj);

    $times = $this->createQueryBuilder('u')
      ->where('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.diff IS NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->andWhere('((u.start BETWEEN :startFrom AND :startTo OR u.stop BETWEEN :endFrom AND :endTo) OR (u.start < :startFrom AND u.stop > :endTo))')
      ->setParameter(':startFrom', $start)
      ->setParameter(':startTo', $stop)
      ->setParameter(':endFrom', $start)
      ->setParameter(':endTo', $stop)
      ->getQuery()
      ->getResult();

    if (!empty($times)) {
      return true;
    }
    return false;

  }
  public function getStopwatchesByProjectCommand($start, $stop, Project $project, Category $category): int {

    $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProjectTeren($start, $stop, $project, $category);
    return count($tasks);

  }
  public function getStopwatchesByProject($start, $stop, Project $project, array $kategorija, int $free = 0 ): array {

    if ($free == 0) {
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProjectFree($start, $stop, $project);
    } else {
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProject($start, $stop, $project);
    }
    $projectStopwatches = [];

    if ($kategorija[0] === 0) {
      foreach ($tasks as $task) {
        if (!is_null($task['log'])) {
          if (!empty ($this->getStopwatchesActive($task['log']))) {

            $projectStopwatches[] = [
              'datum' => $task['datum']->format('d.m.Y.'),
              'zaduzeni' => $task['task']->getAssignedUsers(),
              'klijent' => $task['task']->getProject()->getClient(),
              'stopwatches' => $this->getStopwatchesActive($task['log']),
              'time' => $this->getStopwatchTimeByTask($task['task']),
              'activity' => $this->getStopwatchesActivity($task['log']),
              'description' => $this->getStopwatchesDescription($task['log']),
              'task' => $task,
              'category' => $task['task']->getCategory(),
              'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $project->getCompany())
            ];
          }
        }
      }
    } else {
      foreach ($tasks as $task) {
        if (in_array($task['task']->getCategory(), $kategorija)) {
          if (!is_null($task['log'])) {
            if (!empty ($this->getStopwatchesActive($task['log']))) {
              $projectStopwatches[] = [
                'datum' => $task['datum']->format('d.m.Y.'),
                'zaduzeni' => $task['task']->getAssignedUsers(),
                'klijent' => $task['task']->getProject()->getClient(),
                'stopwatches' => $this->getStopwatchesActive($task['log']),
                'time' => $this->getStopwatchTimeByTask($task['task']),
                'activity' => $this->getStopwatchesActivity($task['log']),
                'description' => $this->getStopwatchesDescription($task['log']),
                'task' => $task,
                'category' => $task['task']->getCategory(),
                'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $project->getCompany())
              ];
            }
          }
        }
      }
    }
    $groupedTasks = [];
    $stopwatchesNiz = [];
    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($groupedTasks[$datum])) {
        $groupedTasks[$datum] = [];
      }

      $groupedTasks[$datum][] = $item;
    }


    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];
      if (!isset($stopwatchesNiz[$datum])) {
        $stopwatchesNiz[$datum] = [];
      }

      foreach ($item['stopwatches'] as $vreme) {
        $stopwatchesNiz[$datum][] = $vreme;
      }

      usort($stopwatchesNiz[$datum], function ($a, $b) {
        return $a['start'] <=> $b['start'];
      });

    }
    $noviStopwatchesNiz = [];
    $brojac = 0;

// Iterirajte kroz postojeći niz i kopirajte podatke sa numeričkim ključem
    foreach ($stopwatchesNiz as $kljuc => $podaci) {
      $noviStopwatchesNiz[$brojac++] = $podaci;
    }

    $countActivities = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($countActivities[$datum])) {
        $countActivities[$datum] = [];
      }

      $countActivities[$datum][] = $item['time'];
    }

    $ukupnoVreme = [];

    foreach ($countActivities as $kljuc => $vreme) {
      $ukupnoSati = 0;
      $ukupnoMinuta = 0;
      $ukupnoSatiR = 0;
      $ukupnoMinutaR = 0;

      foreach ($vreme as $time) {
        $ukupnoSati += (int)$time['hoursTimePriority'];
        $ukupnoMinuta += (int)$time['minutesTimePriority'];
        $ukupnoSatiR += (int)$time['hoursRealTimePriority'];
        $ukupnoMinutaR += (int)$time['minutesRealTimePriority'];
      }

      $ukupnoSati += floor($ukupnoMinuta / 60);
      $ukupnoMinuta = $ukupnoMinuta % 60;

      $ukupnoSatiR += floor($ukupnoMinutaR / 60);
      $ukupnoMinutaR = $ukupnoMinutaR % 60;

      $ukupnoVreme[] = [
        'vreme' => sprintf("%02d:%02d", $ukupnoSati, $ukupnoMinuta),
        'vremeR' => sprintf("%02d:%02d", $ukupnoSatiR, $ukupnoMinutaR)
      ];
    }


    return [$groupedTasks, $ukupnoVreme, $noviStopwatchesNiz, $project->getTitle()];
  }

  public function getStopwatchesByUser($start, $stop, User $user, array $kategorija, int $free = 0 ): array {

    if ($free == 0) {
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUserFree($start, $stop, $user);
    } else {
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);
    }

    $projectStopwatches = [];

    if ($kategorija[0] === 0) {
      foreach ($tasks as $task) {
        if (!is_null($task['log'])) {
          if (!empty ($this->getStopwatchesActive($task['log']))) {
            $projectStopwatches[] = [
              'datum' => $task['datum']->format('d.m.Y.'),
              'zaduzeni' => $task['task']->getAssignedUsers(),
              'klijent' => $task['task']->getProject()->getClient(),
              'stopwatches' => $this->getStopwatchesActive($task['log']),
              'time' => $this->getStopwatchTimeByTaskLog($task['log']),
              'activity' => $this->getStopwatchesActivity($task['log']),
              'description' => $this->getStopwatchesDescription($task['log']),
              'task' => $task,
              'project' => $task['task']->getProject(),
              'category' => $task['task']->getCategory(),
              'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $user->getCompany())
            ];
          }
        }
      }
    } else {
      foreach ($tasks as $task) {
        if (in_array($task['task']->getCategory(), $kategorija)) {
          if (!is_null($task['log'])) {
            if (!empty ($this->getStopwatchesActive($task['log']))) {
              $projectStopwatches[] = [
                'datum' => $task['datum']->format('d.m.Y.'),
                'zaduzeni' => $task['task']->getAssignedUsers(),
                'klijent' => $task['task']->getProject()->getClient(),
                'stopwatches' => $this->getStopwatchesActive($task['log']),
                'time' => $this->getStopwatchTimeByTaskLog($task['log']),
                'activity' => $this->getStopwatchesActivity($task['log']),
                'description' => $this->getStopwatchesDescription($task['log']),
                'task' => $task,
                'project' => $task['task']->getProject(),
                'category' => $task['task']->getCategory(),
                'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $user->getCompany())
              ];
            }
          }
        }
      }
    }
    $groupedTasks = [];
    $stopwatchesNiz = [];
    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($groupedTasks[$datum])) {
        $groupedTasks[$datum] = [];
      }

      $groupedTasks[$datum][] = $item;
    }


    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];
      if (!isset($stopwatchesNiz[$datum])) {
        $stopwatchesNiz[$datum] = [];
      }

      foreach ($item['stopwatches'] as $vreme) {
        $stopwatchesNiz[$datum][] = $vreme;
      }

      usort($stopwatchesNiz[$datum], function ($a, $b) {
        return $a['start'] <=> $b['start'];
      });

    }
    $noviStopwatchesNiz = [];
    $brojac = 0;

// Iterirajte kroz postojeći niz i kopirajte podatke sa numeričkim ključem
    foreach ($stopwatchesNiz as $kljuc => $podaci) {
      $noviStopwatchesNiz[$brojac++] = $podaci;
    }

    $countActivities = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($countActivities[$datum])) {
        $countActivities[$datum] = [];
      }

      $countActivities[$datum][] = $item['time'];
    }

    $ukupnoVreme = [];

    foreach ($countActivities as $kljuc => $vreme) {
      $ukupnoSati = 0;
      $ukupnoMinuta = 0;
      $ukupnoSatiR = 0;
      $ukupnoMinutaR = 0;

      foreach ($vreme as $time) {
        $ukupnoSati += (int)$time['hoursTimePriority'];
        $ukupnoMinuta += (int)$time['minutesTimePriority'];
        $ukupnoSatiR += (int)$time['hoursRealTimePriority'];
        $ukupnoMinutaR += (int)$time['minutesRealTimePriority'];
      }

      $ukupnoSati += floor($ukupnoMinuta / 60);
      $ukupnoMinuta = $ukupnoMinuta % 60;

      $ukupnoSatiR += floor($ukupnoMinutaR / 60);
      $ukupnoMinutaR = $ukupnoMinutaR % 60;

      $ukupnoVreme[] = [
        'vreme' => sprintf("%02d:%02d", $ukupnoSati, $ukupnoMinuta),
        'vremeR' => sprintf("%02d:%02d", $ukupnoSatiR, $ukupnoMinutaR)
      ];
    }


    return [$groupedTasks, $ukupnoVreme, $noviStopwatchesNiz];
  }

  public function getStopwatchesByUserXls($start, $stop, User $user): array {

    $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndUser($start, $stop, $user);

    if (empty($tasks)) {
      return [];
    }

    $projectStopwatches = [];


    foreach ($tasks as $task) {
      if (!is_null($task['log'])) {
        if (!empty ($this->getStopwatchesActive($task['log']))) {
          $projectStopwatches[] = [
            'datum' => $task['datum']->format('d.m.Y.'),
            'zaduzeni' => $task['task']->getAssignedUsers(),
            'klijent' => $task['task']->getProject()->getClient(),
            'stopwatches' => $this->getStopwatchesActive($task['log']),
            'countActivities' => $this->getCountStopwatchesActive($task['log']),
            'time' => $this->getStopwatchTimeByTaskLog($task['log']),
            'activity' => $this->getStopwatchesActivity($task['log']),
            'description' => $this->getStopwatchesDescription($task['log']),
            'task' => $task,
            'category' => $task['task']->getCategory(),
            'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $user->getCompany())
          ];
        }
      }
    }

    $groupedTasks = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($groupedTasks[$datum])) {
        $groupedTasks[$datum] = [];
      }

      $groupedTasks[$datum][] = $item;
    }

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];
      if (!isset($stopwatchesNiz[$datum])) {
        $stopwatchesNiz[$datum] = [];
      }

      foreach ($item['stopwatches'] as $vreme) {
        $stopwatchesNiz[$datum][] = $vreme;
      }

      usort($stopwatchesNiz[$datum], function ($a, $b) {
        return $a['start'] <=> $b['start'];
      });

    }
    $noviStopwatchesNiz = [];
    $brojac = 0;

// Iterirajte kroz postojeći niz i kopirajte podatke sa numeričkim ključem
    foreach ($stopwatchesNiz as $kljuc => $podaci) {
      $noviStopwatchesNiz[$brojac++] = $podaci;
    }

    $countActivities = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($countActivities[$datum])) {
        $countActivities[$datum] = [];
      }

      $countActivities[$datum][] = $item['countActivities'];
    }

    $ukupnoVreme = [];
    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($ukupnoVremeZadatka[$datum])) {
        $ukupnoVremeZadatka[$datum] = [];
      }

      $ukupnoVremeZadatka[$datum][] = $item['time'];
    }

    $rezultati = [];

    foreach ($countActivities as $kljuc => $vrednosti) {
      $ukupnaSuma = array_sum($vrednosti);
      $rezultati[$kljuc] = $ukupnaSuma;
    }

    $ukupnoVreme = [];

    foreach ($ukupnoVremeZadatka as $kljuc => $vreme) {
      $ukupnoSati = 0;
      $ukupnoMinuta = 0;
      $ukupnoSatiR = 0;
      $ukupnoMinutaR = 0;

      foreach ($vreme as $time) {
        $ukupnoSati += (int)$time['hoursTimePriority'];
        $ukupnoMinuta += (int)$time['minutesTimePriority'];
        $ukupnoSatiR += (int)$time['hoursRealTimePriority'];
        $ukupnoMinutaR += (int)$time['minutesRealTimePriority'];
      }

      $ukupnoSati += floor($ukupnoMinuta / 60);
      $ukupnoMinuta = $ukupnoMinuta % 60;

      $ukupnoSatiR += floor($ukupnoMinutaR / 60);
      $ukupnoMinutaR = $ukupnoMinutaR % 60;

      $ukupnoVreme[] = [
        'vreme' => sprintf("%02d:%02d", $ukupnoSati, $ukupnoMinuta),
        'vremeR' => sprintf("%02d:%02d", $ukupnoSatiR, $ukupnoMinutaR)
      ];
    }

    return [$groupedTasks, $rezultati, $ukupnoVreme, $noviStopwatchesNiz];
  }

  public function getStopwatchesByProjectXls($start, $stop, Project $project): array {

    $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProject($start, $stop, $project);

    if (empty($tasks)) {
      return [];
    }


    $projectStopwatches = [];



    foreach ($tasks as $task) {
      if (!is_null($task['log'])) {
        if (!empty ($this->getStopwatchesActive($task['log']))) {
          $projectStopwatches[] = [
            'datum' => $task['datum']->format('d.m.Y.'),
            'zaduzeni' => $task['task']->getAssignedUsers(),
            'klijent' => $task['task']->getProject()->getClient(),
            'stopwatches' => $this->getStopwatchesActive($task['log']),
            'countActivities' => $this->getCountStopwatchesActive($task['log']),
            'time' => $this->getStopwatchTimeByTask($task['task']),
            'activity' => $this->getStopwatchesActivity($task['log']),
            'description' => $this->getStopwatchesDescription($task['log']),
            'task' => $task,
            'category' => $task['task']->getCategory(),
            'dan' => $this->getEntityManager()->getRepository(Holiday::class)->vrstaDana($task['datum'], $project->getCompany())
          ];
        }
      }
    }

    $groupedTasks = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($groupedTasks[$datum])) {
        $groupedTasks[$datum] = [];
      }

      $groupedTasks[$datum][] = $item;
    }

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];
      if (!isset($stopwatchesNiz[$datum])) {
        $stopwatchesNiz[$datum] = [];
      }

      foreach ($item['stopwatches'] as $vreme) {
        $stopwatchesNiz[$datum][] = $vreme;
      }

      usort($stopwatchesNiz[$datum], function ($a, $b) {
        return $a['start'] <=> $b['start'];
      });

    }
    $noviStopwatchesNiz = [];
    $brojac = 0;

// Iterirajte kroz postojeći niz i kopirajte podatke sa numeričkim ključem
    foreach ($stopwatchesNiz as $kljuc => $podaci) {
      $noviStopwatchesNiz[$brojac++] = $podaci;
    }

    $countActivities = [];

    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($countActivities[$datum])) {
        $countActivities[$datum] = [];
      }

      $countActivities[$datum][] = $item['countActivities'];
    }

    $ukupnoVreme = [];
    foreach ($projectStopwatches as $item) {
      $datum = $item['datum'];

      if (!isset($ukupnoVremeZadatka[$datum])) {
        $ukupnoVremeZadatka[$datum] = [];
      }

      $ukupnoVremeZadatka[$datum][] = $item['time'];
    }

    $rezultati = [];

    foreach ($countActivities as $kljuc => $vrednosti) {
      $ukupnaSuma = array_sum($vrednosti);
      $rezultati[$kljuc] = $ukupnaSuma;
    }

    $ukupnoVreme = [];

    foreach ($ukupnoVremeZadatka as $kljuc => $vreme) {
      $ukupnoSati = 0;
      $ukupnoMinuta = 0;
      $ukupnoSatiR = 0;
      $ukupnoMinutaR = 0;

      foreach ($vreme as $time) {
        $ukupnoSati += (int)$time['hoursTimePriority'];
        $ukupnoMinuta += (int)$time['minutesTimePriority'];
        $ukupnoSatiR += (int)$time['hoursRealTimePriority'];
        $ukupnoMinutaR += (int)$time['minutesRealTimePriority'];
      }

      $ukupnoSati += floor($ukupnoMinuta / 60);
      $ukupnoMinuta = $ukupnoMinuta % 60;

      $ukupnoSatiR += floor($ukupnoMinutaR / 60);
      $ukupnoMinutaR = $ukupnoMinutaR % 60;

      $ukupnoVreme[] = [
        'vreme' => sprintf("%02d:%02d", $ukupnoSati, $ukupnoMinuta),
        'vremeR' => sprintf("%02d:%02d", $ukupnoSatiR, $ukupnoMinutaR)
      ];
    }

    return [$groupedTasks, $rezultati, $ukupnoVreme, $noviStopwatchesNiz];
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

      $h = intdiv($time->getDiffRounded(), 60);
      $m = $time->getDiffRounded() % 60;
      $hR = intdiv($time->getDiff(), 60);
      $mR = $time->getDiff() % 60;
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


      $stopwatches [] = [
        'id' => $time->getId(),
        'hours' => $h,
        'minutes' => $m,
        'hoursReal' => $hR,
        'minutesReal' => $mR,
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
        'additionalActivity' => $time->getAdditionalActivity(),
        'client' => $time->getClient(),
      ];
    }
    return $stopwatches;
  }

  public function getEndTime(Task $task): array {

    $logs = $task->getTaskLogs();
    $stopwatches = [];

    foreach ($logs as $log) {

      $time = $this->createQueryBuilder('u')
        ->andWhere('u.taskLog = :taskLog')
        ->setParameter(':taskLog', $log)
        ->andWhere('u.diff is NOT NULL')
        ->andWhere('u.isDeleted = :isDeleted')
        ->setParameter('isDeleted', 0)
        ->orderBy('u.stop', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getResult();


        $user = $time[0]->getTaskLog()->getUser();

        $time = $time[0]->getStop();

        $stopwatches[] = [
          'user' => $user,
          'vreme' => $time
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
      ->orderBy('u.start', 'ASC')
      ->getQuery()
      ->getResult();

    foreach ($times as $time) {

        $h = intdiv($time->getDiffRounded(), 60);
        $m = $time->getDiffRounded() % 60;
        $hR = intdiv($time->getDiff(), 60);
        $mR = $time->getDiff() % 60;
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
      $stopwatches [] = [
        'id' => $time->getId(),
        'hours' => $h,
        'minutes' => $m,
        'hoursReal' => $hR,
        'minutesReal' => $mR,
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
        'additionalActivity' => $time->getAdditionalActivity(),
        'client' => $time->getClient(),
        'category' => $time->getTaskLog()->getTask()->getCategory(),
        'users' => $time->getTaskLog()->getTask()->getAssignedUsers(),
        'project' => $time->getTaskLog()->getTask()->getProject(),
      ];
    }
    return $stopwatches;
  }

  public function getCountStopwatchesActive(TaskLog $taskLog): int {

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.diff is NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->getQuery()
      ->getResult();

    return count($times);
  }

  public function getStopwatchesActivity(TaskLog $taskLog): array {
    $stopwatches = [];

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.diff is NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->getQuery()
      ->getResult();
    $aktivnosti = [];
    $dodatneAktivnosti = [];
    foreach ($times as $time) {
      if(!($time->getActivity()->isEmpty())) {
        foreach ($time->getActivity() as $akt) {
          $aktivnosti[] = [
            $akt->getId() => $akt->getTitle()
          ];
        }
      }
      if (!is_null($time->getAdditionalActivity())) {
        $dodatneAktivnosti[] = [
          $time->getAdditionalActivity()
        ];
      }
    }
      $stopwatches [] = [
        'activity' => $aktivnosti,
        'additionalActivity' => $dodatneAktivnosti,
      ];

    return $aktivnosti;
  }
  public function getStopwatchesDescription(TaskLog $taskLog): array {
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
        'description' => $time->getDescription(),
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

  public function countActiveStopwatches($taskLog): int{
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'))
      ->andWhere('u.taskLog = :taskLog')
      ->setParameter(':taskLog', $taskLog)
      ->andWhere('u.isDeleted = 0')
      ->andWhere('u.diff is NULL');

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

//    $priority = $task->getProject()->getTimerPriority();
//    $priorityTitle = $task->getProject()->getTimerPriorityJson();

    $priorityUserLog = $task->getPriorityUserLog();
    $priorityLogUser = $this->getEntityManager()->getRepository(User::class)->find($priorityUserLog);



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


//    if ($priority == TimerPriorityData::FIRST_ASSIGN) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneBy(['task' => $task], ['id' => 'ASC']);
//    } elseif ($priority == TimerPriorityData::ROLE_GEO) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 1);
//    } elseif ($priority == TimerPriorityData::ROLE_FIG) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 2);
//    }

    $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneBy(['task' => $task, 'user' => $priorityLogUser]);

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
      if ($htP < 10) {
        $htP = '0' . $htP;
      }
      if ($mtP < 10) {
        $mtP = '0' . $mtP;
      }
      if ($hRtP < 10) {
        $hRtP = '0' . $hRtP;
      }
      if ($mRtP < 10) {
        $mRtP = '0' . $mRtP;
      }


      return [
        'hours' => $h,
        'minutes' => $m,
        'hoursReal' => $hR,
        'minutesReal' => $mR,
        'hoursTimePriority' => $htP,
        'minutesTimePriority' => $mtP,
        'hoursRealTimePriority' => $hRtP,
        'minutesRealTimePriority' => $mRtP,
        'priority' => $priorityLogUser->getFullName()
      ];
    }
    return [];
  }

  public function getStopwatchTimeByTaskLog(TaskLog $taskLog ): array {

//    $priority = $task->getProject()->getTimerPriority();
//    $priorityTitle = $task->getProject()->getTimerPriorityJson();

    $priorityUserLog = $taskLog;
    $priorityLogUser = $taskLog->getUser();



    $hoursTotalRounded = 0;
    $minutesTotalRounded = 0;
    $hoursTotalReal = 0;
    $minutesTotalReal = 0;

    $hoursRounded = 0;
    $minutesRounded = 0;
    $hoursReal = 0;
    $minutesReal = 0;

    $logs = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $taskLog->getTask()]);

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


//    if ($priority == TimerPriorityData::FIRST_ASSIGN) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneBy(['task' => $task], ['id' => 'ASC']);
//    } elseif ($priority == TimerPriorityData::ROLE_GEO) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 1);
//    } elseif ($priority == TimerPriorityData::ROLE_FIG) {
//      $logPriority = $this->getEntityManager()->getRepository(TaskLog::class)->findOneByUserPosition($task, 2);
//    }

    $logPriority = $taskLog;

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
      if ($htP < 10) {
        $htP = '0' . $htP;
      }
      if ($mtP < 10) {
        $mtP = '0' . $mtP;
      }
      if ($hRtP < 10) {
        $hRtP = '0' . $hRtP;
      }
      if ($mRtP < 10) {
        $mRtP = '0' . $mRtP;
      }


      return [
        'hours' => $h,
        'minutes' => $m,
        'hoursReal' => $hR,
        'minutesReal' => $mR,
        'hoursTimePriority' => $htP,
        'minutesTimePriority' => $mtP,
        'hoursRealTimePriority' => $hRtP,
        'minutesRealTimePriority' => $mRtP,
        'priority' => $priorityLogUser->getFullName()
      ];
    }
    return [];
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

  public function setTimeManual(StopwatchTime $stopwatch, string $range): StopwatchTime {


    list($pocetak, $kraj) = explode(' - ', $range);

    $format = 'd.m.Y H:i';

    $start = DateTimeImmutable::createFromFormat($format, $pocetak);
    $stop = DateTimeImmutable::createFromFormat($format, $kraj);

    $task = $stopwatch->getTaskLog()->getTask();
    $project = $task->getProject();


    $stopwatch->setStart($start);
    $stopwatch->setStop($stop);

    $days = $stopwatch->getStart()->diff($stopwatch->getStop())->d;
    $hours = $stopwatch->getStart()->diff($stopwatch->getStop())->h;
    $hours = $days*24 + $hours;
    $minutes = $stopwatch->getStart()->diff($stopwatch->getStop())->i;

    $stopwatch = $this->getEntityManager()->getRepository(StopwatchTime::class)->setTime($stopwatch, $hours, $minutes);

    return $stopwatch;
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

  public function findAllToCheck(): array {
    $company = $this->security->getUser()->getCompany();
    $now = new DateTimeImmutable();
    $oneMonthAgo = new DateTimeImmutable('-1 month');
    $stopwatches = [];

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.diff < :dif')
      ->orWhere('u.diff > :dif1')
      ->setParameter(':dif', 10)
      ->setParameter(':dif1', 540)
      ->andWhere('u.diff is NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->andWhere('u.stop BETWEEN :start AND :end')
      ->andWhere('u.company = :company')
      ->andWhere('u.checked IS NULL')
      ->setParameter('start', $oneMonthAgo)
      ->setParameter('end', $now)
      ->setParameter('company', $company)
      ->orderBy('u.created', 'DESC')
      ->getQuery()
      ->getResult();

    foreach ($times as $time) {
      $hours = floor($time->getDiff() / 60);
      $remainingMinutes = $time->getDiff() % 60;

      $diff = sprintf('%02d:%02d', $hours, $remainingMinutes);

      $stopwatches [] = [
        'id' => $time->getId(),
        'user' => $time->getTaskLog()->getUser(),
        'task' => $time->getTaskLog()->getTask(),
        'taskLog' => $time->getTaskLog(),
        'start' => $time->getStart(),
        'stop' => $time->getStop(),
        'diff' => $diff
      ];
    }

    return $stopwatches;

  }

  public function findAllToCheckCount(): int {
    $company = $this->security->getUser()->getCompany();
    $now = new DateTimeImmutable();
    $oneMonthAgo = new DateTimeImmutable('-1 month');

    $times = $this->createQueryBuilder('u')
      ->andWhere('u.diff < :dif')
      ->orWhere('u.diff > :dif1')
      ->setParameter(':dif', 10)
      ->setParameter(':dif1', 540)
      ->andWhere('u.diff is NOT NULL')
      ->andWhere('u.isDeleted = 0')
      ->andWhere('u.stop BETWEEN :start AND :end')
      ->andWhere('u.company = :company')
      ->andWhere('u.checked IS NULL')
      ->setParameter('start', $oneMonthAgo)
      ->setParameter('end', $now)
      ->setParameter('company', $company)
      ->orderBy('u.created', 'DESC')
      ->getQuery()
      ->getResult();


    return count($times);

  }

  public function getStopwatchByUserCheck(User $user): bool {

    $currentTime = new DateTimeImmutable();

    $startDate = $currentTime->add(new DateInterval('PT1M'))->format('Y-m-d H:i:s');
    $endDate = $currentTime->sub(new DateInterval('PT1M'))->format('Y-m-d H:i:s');

    $tasks =  $this->createQueryBuilder('t')
      ->where('t.createdBy = :user')
      ->andWhere('t.isDeleted <> 1')
      ->andWhere('t.created < :startDate')
      ->andWhere('t.created > :endDate')
      ->setParameter(':startDate', $startDate)
      ->setParameter(':endDate', $endDate)
      ->setParameter(':user', $user)
      ->addOrderBy('t.id', 'DESC')
      ->getQuery()
      ->getResult();

    if (!empty($tasks)){
      return true;
    }
    return false;
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
