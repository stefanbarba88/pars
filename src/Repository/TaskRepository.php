<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\Car;
use App\Entity\FastTask;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskHistory;
use App\Entity\TaskLog;
use App\Entity\Tool;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Task::class);
  }

  public function taskStatus(Task $task): int {

    $status = [];
    foreach ($task->getTaskLogs() as $log) {
      $stopwatches = $this->getEntityManager()->getRepository(StopwatchTime::class)->findBy(['taskLog' => $log]);

      if (!empty($stopwatches)) {
        foreach ($stopwatches as $stopwatch) {
          if (is_null($stopwatch->getStart()) && is_null($stopwatch->getStop()) && is_null($stopwatch->getDiff())) {
            $status[] = TaskStatusData::NIJE_ZAPOCETO;
          }
          if (!is_null($stopwatch->getStart()) && is_null($stopwatch->getStop())) {
            $status[] = TaskStatusData::ZAPOCETO;
          }
          if (!is_null($stopwatch->getDiff())) {
            $status[] = TaskStatusData::ZAVRSENO;
          }
        }
      } else {
        $status[] = TaskStatusData::NIJE_ZAPOCETO;
      }
    }

    $status = array_unique($status);
    if (count(array_filter($status, function($value) { return $value !== 0; })) === 0) {
      return TaskStatusData::NIJE_ZAPOCETO;
    }
    if (count(array_filter($status, function($value) { return $value !== 2; })) === 0) {
      return TaskStatusData::ZAVRSENO;
    }
    if (in_array(1, $status)) {
      return TaskStatusData::ZAPOCETO;
    }
    if (in_array(0, $status) && count(array_filter($status, function($value) { return $value !== 0; })) !== count($status)) {
      return TaskStatusData::ZAPOCETO;
    }
    return TaskStatusData::NIJE_ZAPOCETO;
//    $stopwatches = $this->createQueryBuilder('t')
//
//      ->select('s.diff', 's.start', 's.stop')
//      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
//      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
//      ->andWhere('t.id = :taskId')
//      ->andWhere('s.isDeleted = 0')
//      ->setParameter(':taskId', $task->getId())
//      ->addOrderBy('s.created', 'DESC')
//      ->getQuery()
//      ->getResult();
//
//
//
//    if (!empty($stopwatches)) {
//      foreach ($stopwatches as $stopwatch) {
//        if (is_null($stopwatch['start']) && is_null($stopwatch['stop']) && is_null($stopwatch['diff'])) {
//          $status[] = TaskStatusData::NIJE_ZAPOCETO;
//        }
//        if (!is_null($stopwatch['start']) && is_null($stopwatch['stop'])) {
//          $status[] = TaskStatusData::ZAPOCETO;
//        }
//        if (!is_null($stopwatch['diff'])) {
//          $status[] = TaskStatusData::ZAVRSENO;
//        }
//      }
//      return $this->checkArrayValues($status);
//
//    } else {
//      return TaskStatusData::NIJE_ZAPOCETO;
//    }
//
//

  }

  public function getPdfsByTask(Task $task): array {

    $pdfs = $this->createQueryBuilder('t')
      ->select('i.title', 'i.path')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('t.id = :taskId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

    $pdfsTask = $this->createQueryBuilder('t')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 't = i.task')
      ->andWhere('t.id = :taskId')
      ->andWhere('i.stopwatchTime IS NULL')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

    return array_merge($pdfs, $pdfsTask);

  }

  public function close(Task $task): void {

    $stopwatches = $this->createQueryBuilder('t')
      ->select('s.id')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->andWhere('t.id = :taskId')
      ->andWhere('s.start is NOT NULL')
      ->andWhere('s.diff is NULL')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

    foreach ($stopwatches as $stop) {
      $stopwatch = $this->getEntityManager()->getRepository(StopwatchTime::class)->find($stop);
      $user = $stopwatch->getTaskLog()->getUser();
      $user->setIsInTask(false);
      $this->getEntityManager()->getRepository(User::class)->save($user);
      $this->getEntityManager()->getRepository(StopwatchTime::class)->close($stopwatch);
    }

  }

  public function deleteTask(Task $task, User $user): Task {

    $stopwatches = $this->createQueryBuilder('t')
      ->select('s.id')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->andWhere('t.id = :taskId')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

    foreach ($stopwatches as $stop) {
      $stopwatch = $this->getEntityManager()->getRepository(StopwatchTime::class)->find($stop);
      if (is_null($stopwatch->getDiff() && !is_null($stopwatch->getStart()))) {
        $user = $stopwatch->getTaskLog()->getUser();
        $user->setIsInTask(false);
        $this->getEntityManager()->getRepository(User::class)->save($user);
        $this->getEntityManager()->getRepository(StopwatchTime::class)->close($stopwatch);
      }

      $this->getEntityManager()->getRepository(StopwatchTime::class)->deleteStopwatch($stopwatch, $user);
    }

    $task->setIsDeleted(true);
    return $this->save($task);
  }

  public function getImagesByTask(Task $task): array {

    return $this->createQueryBuilder('t')
      ->select('i.thumbnail100', 'i.thumbnail500', 'i.thumbnail1024')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Image::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('t.id = :taskId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

  }

  public function getTasksByProject(Project $project): array {

    $list = [];

    $tasks = $this->getEntityManager()->getRepository(Task::class)->findBy(['project' => $project], ['isDeleted' => 'ASC', 'isClosed' => 'ASC', 'isPriority' => 'DESC', 'id' => 'DESC']);
    foreach ($tasks as $task) {
      $logs = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $task]);
      $assigners = [];
      foreach ($logs as $log) {
         $assigners[] = $log->getUser();
      }

      $list[] = ['task' => $task, 'status' => $this->taskStatus($task), 'users' => $assigners];
    }

    usort($list, function ($a, $b) {
      return $a['status'] <=> $b['status'];
    });
    return $list;
  }

  public function getTasksByUser(User $user): array {

    $list = [];
    $tasks =  $this->createQueryBuilder('t')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->andWhere('tl.user = :userId')
      ->setParameter(':userId', $user->getId())
      ->addOrderBy('t.isDeleted', 'ASC')
      ->addOrderBy('t.isClosed', 'ASC')
      ->addOrderBy('t.isPriority', 'DESC')
      ->addOrderBy('t.id', 'DESC')
      ->getQuery()
      ->getResult();

    foreach ($tasks as $task) {
      $list[] = ['task' => $task, 'status' => $this->taskStatus($task)];
    }
    usort($list, function ($a, $b) {
      return $a['status'] <=> $b['status'];
    });
    return $list;
  }
  public function getTasks(): array {

    $list = [];
    $tasks =  $this->createQueryBuilder('t')
      ->addOrderBy('t.isDeleted', 'ASC')
      ->addOrderBy('t.isClosed', 'ASC')
      ->addOrderBy('t.isPriority', 'DESC')
      ->addOrderBy('t.id', 'DESC')
      ->getQuery()
      ->getResult();

    foreach ($tasks as $task) {
      $list[] = ['task' => $task, 'status' => $this->taskStatus($task)];
    }
    usort($list, function ($a, $b) {
      return $a['status'] <=> $b['status'];
    });
    return $list;
  }

  public function saveTask(Task $task, User $user, ?string $history): Task  {

    $taskLogOld = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $task]);
    foreach ($taskLogOld as $log) {
      $task->removeTaskLog($log);
    }

    foreach ($task->getAssignedUsers() as $assignedUser) {
      $taskLog = new TaskLog();
      $taskLog->setUser($assignedUser);
      $task->addTaskLog($taskLog);
    }

    if (!is_null($task->getId())) {

      $historyTask = new TaskHistory();
      $historyTask->setHistory($history);

      $task->addTaskHistory($historyTask);
      $task->setEditBy($user);

      return $this->save($task);
    }

    $task->setCreatedBy($user);

    return $this->save($task);

  }

  public function saveTaskFromList(Task $task, User $user): Task  {

    $taskLogOld = $this->getEntityManager()->getRepository(TaskLog::class)->findBy(['task' => $task]);
    foreach ($taskLogOld as $log) {
      $task->removeTaskLog($log);
    }

    foreach ($task->getAssignedUsers() as $assignedUser) {
      $taskLog = new TaskLog();
      $taskLog->setUser($assignedUser);
      $task->addTaskLog($taskLog);
    }

    $task->setCreatedBy($user);

    return $this->save($task);

  }
  public function saveTaskInfo(Task $task, User $user, ?string $history): Task  {

    if (!is_null($task->getId())) {

      $historyTask = new TaskHistory();
      $historyTask->setHistory($history);

      $task->addTaskHistory($historyTask);
      $task->setEditBy($user);

      return $this->save($task);
    }

    $task->setCreatedBy($user);

    return $this->save($task);

  }

  public function changeStatus(Task $task, int $status): Task  {

    $task->setStatus($status);
    return $this->save($task);

  }

  public function getAssignedUsersByTask(Task $task): array  {
    $niz = [];

    $users = $task->getAssignedUsers();

    foreach ($users as $user) {
      $niz[] = $user->getId();
    }

    $primary = $task->getPriorityUserLog();

    $nizBezElementa = array_diff($niz, [$primary]);
    $nizBezElementa = array_values($nizBezElementa);

    $user1 = $this->getEntityManager()->getRepository(User::class)->find($primary);

    if (isset($nizBezElementa[0])) {
      $user2 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[0]);
    } else {
      $user2 = null;
    }
    if (isset($nizBezElementa[1])) {
      $user3 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[1]);
    } else {
      $user3 = null;
    }

    if (isset($nizBezElementa[2])) {
      $user4 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[2]);
    } else {
      $user4 = null;
    }

    if (isset($nizBezElementa[3])) {
      $user5 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[3]);
    } else {
      $user5 = null;
    }

    if (isset($nizBezElementa[4])) {
      $user6 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[4]);
    } else {
      $user6 = null;
    }

    if (isset($nizBezElementa[5])) {
      $user7 = $this->getEntityManager()->getRepository(User::class)->find($nizBezElementa[5]);
    } else {
      $user7 = null;
    }

    return [$user1, $user2, $user3, $user4, $user5, $user6, $user7];

  }


  public function getTasksByDateAndProject(DateTimeImmutable $start, DateTimeImmutable $stop, Project $project): array  {

    $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
      ->andWhere('t.project = :project')
      ->andWhere('t.isDeleted <> 1')
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->setParameter('project', $project->getId())
      ->orderBy('t.time', 'ASC');

    $query = $qb->getQuery();
    $taskovi = $query->getResult();

    $lista = [];
    foreach ($taskovi as $tsk) {
      $primaryUser = $this->getEntityManager()->getRepository(User::class)->find($tsk->getPriorityUserLog());
       $lista[] = [
         'task' => $tsk,
         'datum' => $tsk->getDatumKreiranja(),
         'status' => $this->taskStatus($tsk),
         'log' => $this->getEntityManager()->getRepository(TaskLog::class)->findOneBy(['task' => $tsk, 'user' => $primaryUser]),
       ];
    }
    return $lista;
  }
  public function getTasksByDate(DateTimeImmutable $date): array  {

    $startDate = $date->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $date->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('t');
    $qb
      ->where($qb->expr()->between('t.datumKreiranja', ':start', ':end'))
      ->setParameter('start', $startDate)
      ->setParameter('end', $endDate)
      ->orderBy('t.time', 'ASC');

    $query = $qb->getQuery();
    $taskovi = $query->getResult();
    $lista = [];
    foreach ($taskovi as $tsk) {
       $lista[] = [
         'task' => $tsk,
         'status' => $this->taskStatus($tsk),
         'logStatus' => $this->getEntityManager()->getRepository(TaskLog::class)->getLogStatus($tsk),
         'car' => $this->getEntityManager()->getRepository(Car::class)->findBy(['id' => $tsk->getCar()]),
         'driver' => $this->getEntityManager()->getRepository(User::class)->findBy(['id' => $tsk->getDriver()])
       ];
    }
//    dd($lista);
    return $lista;
  }

  public function getTasksByFastTask(FastTask $fastTask): array  {

    $lista = [];
    $taskovi = [];

    $lista[] = $fastTask->getTask1();
    $lista[] = $fastTask->getTask2();
    $lista[] = $fastTask->getTask3();
    $lista[] = $fastTask->getTask4();
    $lista[] = $fastTask->getTask5();
    $lista[] = $fastTask->getTask6();
    $lista[] = $fastTask->getTask7();
    $lista[] = $fastTask->getTask8();
    $lista[] = $fastTask->getTask9();
    $lista[] = $fastTask->getTask10();


    foreach ($lista as $tsk) {
      if (!is_null($tsk)) {
        $taskovi[] = [
          'id' => $tsk,
          'status' => $this->taskStatus($this->getEntityManager()->getRepository(Task::class)->find($tsk))
        ];
      }
    }

    return $taskovi;
  }

  public function createTasksFromList(FastTask $fastTask, User $user): FastTask  {
    $format = "H:i";
   if(!is_null($fastTask->getProject1())) {
      $task1 = new Task();
      $project1 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject1());
      $task1->setProject($project1);
      $task1->setDatumKreiranja($fastTask->getDatum());
      $task1->setTitle($project1->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task1->setIsTimeRoundUp(true);
      $task1->setRoundingInterval(15);
      $task1->setCreatedBy($user);


      if(!is_null($fastTask->getDescription1())) {
        $task1->setDescription($fastTask->getDescription1());
      }

      if(!is_null($fastTask->getGeo11())) {
        $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo11()));
        $task1->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo11())->getId());
      }
      if(!is_null($fastTask->getGeo21())) {
        $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo21()));
      }
      if(!is_null($fastTask->getGeo31())) {
        $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo31()));
      }
      if(!empty($fastTask->getActivity1())) {
        foreach ($fastTask->getActivity1() as $act) {
          $task1->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }
      if(!empty($fastTask->getOprema1())) {
       foreach ($fastTask->getOprema1() as $opr) {
         $task1->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }

     if(!is_null($fastTask->getTime1())) {
       $task1->setTime($fastTask->getDatum()->modify($fastTask->getTime1()));
     } else {
       $task1->setTime($fastTask->getDatum());
     }


     if(!is_null($fastTask->getCar1())) {
       $task1->setCar($fastTask->getCar1());
       if (!is_null($task1->getDriver())) {
         $task1->setDriver($fastTask->getDriver1());
       } else {
         $task1->setDriver($fastTask->getGeo11());
       }
     }

     $task1 = $this->saveTaskFromList($task1, $user);
     $fastTask->setTask1($task1->getId());
   }

   if(!is_null($fastTask->getProject2())) {
      $task2 = new Task();
      $project2 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject2());
      $task2->setProject($project2);
      $task2->setDatumKreiranja($fastTask->getDatum());
      $task2->setTitle($project2->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task2->setCreatedBy($user);
      $task2->setIsTimeRoundUp(true);
      $task2->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription2())) {
        $task2->setDescription($fastTask->getDescription2());
      }
     if(!empty($fastTask->getOprema2())) {
       foreach ($fastTask->getOprema2() as $opr) {
         $task2->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo12())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo12()));
        $task2->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo12())->getId());
      }
      if(!is_null($fastTask->getGeo22())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo22()));
      }
      if(!is_null($fastTask->getGeo32())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo32()));
      }
      if(!empty($fastTask->getActivity2())) {
        foreach ($fastTask->getActivity2() as $act) {
          $task2->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime2())) {
       $task2->setTime($fastTask->getDatum()->modify($fastTask->getTime2()));
     } else {
         $task2->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar2())) {
       $task2->setCar($fastTask->getCar2());
       if (!is_null($task2->getDriver())) {
         $task2->setDriver($fastTask->getDriver2());
       } else {
         $task2->setDriver($fastTask->getGeo12());
       }
     }
     $task2 = $this->saveTaskFromList($task2, $user);
     $fastTask->setTask2($task2->getId());
    }

   if(!is_null($fastTask->getProject3())) {
      $task3 = new Task();
      $project3 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject3());
      $task3->setProject($project3);
      $task3->setDatumKreiranja($fastTask->getDatum());
      $task3->setTitle($project3->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task3->setCreatedBy($user);
      $task3->setIsTimeRoundUp(true);
      $task3->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription3())) {
        $task3->setDescription($fastTask->getDescription3());
      }
     if(!empty($fastTask->getOprema3())) {
       foreach ($fastTask->getOprema3() as $opr) {
         $task3->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo13())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo13()));
        $task3->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo13())->getId());
      }
      if(!is_null($fastTask->getGeo23())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo23()));
      }
      if(!is_null($fastTask->getGeo33())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo33()));
      }
      if(!empty($fastTask->getActivity3())) {
        foreach ($fastTask->getActivity3() as $act) {
          $task3->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime3())) {
       $task3->setTime($fastTask->getDatum()->modify($fastTask->getTime3()));
     } else {
       $task3->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar3())) {
       $task3->setCar($fastTask->getCar3());
       if (!is_null($task3->getDriver())) {
         $task3->setDriver($fastTask->getDriver3());
       } else {
         $task3->setDriver($fastTask->getGeo13());
       }
     }
     $task3 = $this->saveTaskFromList($task3, $user);
     $fastTask->setTask3($task3->getId());
    }

   if(!is_null($fastTask->getProject4())) {
      $task4 = new Task();
      $project4 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject4());
      $task4->setProject($project4);
      $task4->setDatumKreiranja($fastTask->getDatum());
      $task4->setTitle($project4->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task4->setCreatedBy($user);
      $task4->setIsTimeRoundUp(true);
      $task4->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription4())) {
        $task4->setDescription($fastTask->getDescription4());
      }
     if(!empty($fastTask->getOprema4())) {
       foreach ($fastTask->getOprema4() as $opr) {
         $task4->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo14())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo14()));
        $task4->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo14())->getId());
      }
      if(!is_null($fastTask->getGeo24())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo24()));
      }
      if(!is_null($fastTask->getGeo34())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo34()));
      }
      if(!empty($fastTask->getActivity4())) {
        foreach ($fastTask->getActivity4() as $act) {
          $task4->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime4())) {
       $task4->setTime($fastTask->getDatum()->modify($fastTask->getTime4()));
     } else {
       $task4->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar4())) {
       $task4->setCar($fastTask->getCar4());
       if (!is_null($task4->getDriver())) {
         $task4->setDriver($fastTask->getDriver4());
       } else {
         $task4->setDriver($fastTask->getGeo14());
       }
     }
     $task4 = $this->saveTaskFromList($task4, $user);
     $fastTask->setTask4($task4->getId());
    }

   if(!is_null($fastTask->getProject5())) {
      $task5 = new Task();
      $project5 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject5());
      $task5->setProject($project5);
      $task5->setDatumKreiranja($fastTask->getDatum());
      $task5->setTitle($project5->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task5->setCreatedBy($user);
      $task5->setIsTimeRoundUp(true);
      $task5->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription5())) {
        $task5->setDescription($fastTask->getDescription5());
      }
     if(!empty($fastTask->getOprema5())) {
       foreach ($fastTask->getOprema5() as $opr) {
         $task5->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo15())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo15()));
        $task5->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo15())->getId());
      }
      if(!is_null($fastTask->getGeo25())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo25()));
      }
      if(!is_null($fastTask->getGeo35())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo35()));
      }
      if(!empty($fastTask->getActivity5())) {
        foreach ($fastTask->getActivity5() as $act) {
          $task5->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime5())) {
       $task5->setTime($fastTask->getDatum()->modify($fastTask->getTime5()));
     } else {
       $task5->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar5())) {
       $task5->setCar($fastTask->getCar5());
       if (!is_null($task5->getDriver())) {
         $task5->setDriver($fastTask->getDriver5());
       } else {
         $task5->setDriver($fastTask->getGeo15());
       }
     }
     $task5 = $this->saveTaskFromList($task5, $user);
     $fastTask->setTask5($task5->getId());
    }

   if(!is_null($fastTask->getProject6())) {
      $task6 = new Task();
      $project6 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject6());
      $task6->setProject($project6);
      $task6->setDatumKreiranja($fastTask->getDatum());
      $task6->setTitle($project6->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task6->setCreatedBy($user);
      $task6->setIsTimeRoundUp(true);
      $task6->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription6())) {
        $task6->setDescription($fastTask->getDescription6());
      }
     if(!empty($fastTask->getOprema6())) {
       foreach ($fastTask->getOprema6() as $opr) {
         $task6->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo16())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo16()));
        $task6->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo16())->getId());
      }
      if(!is_null($fastTask->getGeo26())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo26()));
      }
      if(!is_null($fastTask->getGeo36())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo36()));
      }
      if(!empty($fastTask->getActivity6())) {
        foreach ($fastTask->getActivity6() as $act) {
          $task6->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime6())) {
       $task6->setTime($fastTask->getDatum()->modify($fastTask->getTime6()));
     } else {
       $task6->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar6())) {
       $task6->setCar($fastTask->getCar6());
       if (!is_null($task6->getDriver())) {
         $task6->setDriver($fastTask->getDriver6());
       } else {
         $task6->setDriver($fastTask->getGeo16());
       }
     }
     $task6 = $this->saveTaskFromList($task6, $user);
     $fastTask->setTask6($task6->getId());
    }

   if(!is_null($fastTask->getProject7())) {
      $task7 = new Task();
      $project7 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject7());
      $task7->setProject($project7);
      $task7->setDatumKreiranja($fastTask->getDatum());
      $task7->setTitle($project7->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task7->setCreatedBy($user);
      $task7->setIsTimeRoundUp(true);
      $task7->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription7())) {
        $task7->setDescription($fastTask->getDescription7());
      }
     if(!empty($fastTask->getOprema7())) {
       foreach ($fastTask->getOprema7() as $opr) {
         $task7->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo17())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo17()));
        $task7->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo17())->getId());
      }
      if(!is_null($fastTask->getGeo27())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo27()));
      }
      if(!is_null($fastTask->getGeo37())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo37()));
      }
      if(!empty($fastTask->getActivity7())) {
        foreach ($fastTask->getActivity7() as $act) {
          $task7->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime7())) {
       $task7->setTime($fastTask->getDatum()->modify($fastTask->getTime7()));
     } else {
       $task7->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar7())) {
       $task7->setCar($fastTask->getCar7());
       if (!is_null($task7->getDriver())) {
         $task7->setDriver($fastTask->getDriver7());
       } else {
         $task7->setDriver($fastTask->getGeo17());
       }
     }
     $task7 = $this->saveTaskFromList($task7, $user);
     $fastTask->setTask7($task7->getId());
    }

   if(!is_null($fastTask->getProject8())) {
      $task8 = new Task();
      $project8 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject8());
      $task8->setProject($project8);
      $task8->setDatumKreiranja($fastTask->getDatum());
      $task8->setTitle($project8->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task8->setCreatedBy($user);
      $task8->setIsTimeRoundUp(true);
      $task8->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription8())) {
        $task8->setDescription($fastTask->getDescription8());
      }
     if(!empty($fastTask->getOprema8())) {
       foreach ($fastTask->getOprema8() as $opr) {
         $task8->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo18())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo18()));
        $task8->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo18())->getId());
      }
      if(!is_null($fastTask->getGeo28())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo28()));
      }
      if(!is_null($fastTask->getGeo38())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo38()));
      }
      if(!empty($fastTask->getActivity8())) {
        foreach ($fastTask->getActivity8() as $act) {
          $task8->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime8())) {
       $task8->setTime($fastTask->getDatum()->modify($fastTask->getTime8()));
     } else {
       $task8->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar8())) {
       $task8->setCar($fastTask->getCar8());
       if (!is_null($task8->getDriver())) {
         $task8->setDriver($fastTask->getDriver8());
       } else {
         $task8->setDriver($fastTask->getGeo18());
       }
     }
     $task8 = $this->saveTaskFromList($task8, $user);
     $fastTask->setTask8($task8->getId());
    }

   if(!is_null($fastTask->getProject9())) {
      $task9 = new Task();
      $project9 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject9());
      $task9->setProject($project9);
      $task9->setDatumKreiranja($fastTask->getDatum());
      $task9->setTitle($project9->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task9->setCreatedBy($user);
      $task9->setIsTimeRoundUp(true);
      $task9->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription9())) {
        $task9->setDescription($fastTask->getDescription9());
      }
     if(!empty($fastTask->getOprema9())) {
       foreach ($fastTask->getOprema9() as $opr) {
         $task9->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo19())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo19()));
        $task9->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo19())->getId());
      }
      if(!is_null($fastTask->getGeo29())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo29()));
      }
      if(!is_null($fastTask->getGeo39())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo39()));
      }
      if(!empty($fastTask->getActivity9())) {
        foreach ($fastTask->getActivity9() as $act) {
          $task9->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime9())) {
       $task9->setTime($fastTask->getDatum()->modify($fastTask->getTime9()));
     } else {
       $task9->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar9())) {
       $task9->setCar($fastTask->getCar9());
       if (!is_null($task9->getDriver())) {
         $task9->setDriver($fastTask->getDriver9());
       } else {
         $task9->setDriver($fastTask->getGeo19());
       }
     }
     $task9 = $this->saveTaskFromList($task9, $user);
     $fastTask->setTask9($task9->getId());
    }

   if(!is_null($fastTask->getProject10())) {
      $task10 = new Task();
      $project10 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject10());
      $task10->setProject($project10);
      $task10->setDatumKreiranja($fastTask->getDatum());
      $task10->setTitle($project10->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task10->setCreatedBy($user);
      $task10->setIsTimeRoundUp(true);
      $task10->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription10())) {
        $task10->setDescription($fastTask->getDescription10());
      }
     if(!empty($fastTask->getOprema10())) {
       foreach ($fastTask->getOprema10() as $opr) {
         $task10->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
       }
     }
      if(!is_null($fastTask->getGeo110())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo110()));
        $task10->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo110())->getId());
      }
      if(!is_null($fastTask->getGeo210())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo210()));
      }
      if(!is_null($fastTask->getGeo310())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo310()));
      }
      if(!empty($fastTask->getActivity10())) {
        foreach ($fastTask->getActivity10() as $act) {
          $task10->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

     if(!is_null($fastTask->getTime10())) {
       $task10->setTime($fastTask->getDatum()->modify($fastTask->getTime10()));
     } else {
       $task10->setTime($fastTask->getDatum());
     }
     if(!is_null($fastTask->getCar10())) {
       $task10->setCar($fastTask->getCar10());
       if (!is_null($task10->getDriver())) {
         $task10->setDriver($fastTask->getDriver10());
       } else {
         $task10->setDriver($fastTask->getGeo110());
       }
     }
     $task10 = $this->saveTaskFromList($task10, $user);
     $fastTask->setTask10($task10->getId());
    }

   $fastTask->setStatus(FastTaskData::SAVED);
   return $this->getEntityManager()->getRepository(FastTask::class)->save($fastTask);

  }

  public function createTasksFromListEdited(FastTask $fastTask, User $user): FastTask  {
    $format = "H:i";
    if ($fastTask->getTask1() == 0) {
      if (!is_null($fastTask->getProject1())) {
        $task1 = new Task();
        $project1 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject1());
        $task1->setProject($project1);
        $task1->setDatumKreiranja($fastTask->getDatum());
        $task1->setTitle($project1->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
        $task1->setIsTimeRoundUp(true);
        $task1->setRoundingInterval(15);
        $task1->setCreatedBy($user);


        if (!is_null($fastTask->getDescription1())) {
          $task1->setDescription($fastTask->getDescription1());
        }

        if (!is_null($fastTask->getGeo11())) {
          $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo11()));
          $task1->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo11())->getId());
        }
        if (!is_null($fastTask->getGeo21())) {
          $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo21()));
        }
        if (!is_null($fastTask->getGeo31())) {
          $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo31()));
        }
        if (!empty($fastTask->getActivity1())) {
          foreach ($fastTask->getActivity1() as $act) {
            $task1->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
          }
        }
        if (!empty($fastTask->getOprema1())) {
          foreach ($fastTask->getOprema1() as $opr) {
            $task1->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
          }
        }

        if (!is_null($fastTask->getTime1())) {
          $task1->setTime($fastTask->getDatum()->modify($fastTask->getTime1()));
        } else {
          $task1->setTime($fastTask->getDatum());
        }


        if (!is_null($fastTask->getCar1())) {
          $task1->setCar($fastTask->getCar1());
          if (!is_null($task1->getDriver())) {
            $task1->setDriver($fastTask->getDriver1());
          } else {
            $task1->setDriver($fastTask->getGeo11());
          }
        }

        $task1 = $this->saveTaskFromList($task1, $user);
        $fastTask->setTask1($task1->getId());
      }
    }
    if ($fastTask->getTask2() == 0) {
      if(!is_null($fastTask->getProject2())) {
      $task2 = new Task();
      $project2 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject2());
      $task2->setProject($project2);
      $task2->setDatumKreiranja($fastTask->getDatum());
      $task2->setTitle($project2->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task2->setCreatedBy($user);
      $task2->setIsTimeRoundUp(true);
      $task2->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription2())) {
        $task2->setDescription($fastTask->getDescription2());
      }
      if(!empty($fastTask->getOprema2())) {
        foreach ($fastTask->getOprema2() as $opr) {
          $task2->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo12())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo12()));
        $task2->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo12())->getId());
      }
      if(!is_null($fastTask->getGeo22())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo22()));
      }
      if(!is_null($fastTask->getGeo32())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo32()));
      }
      if(!empty($fastTask->getActivity2())) {
        foreach ($fastTask->getActivity2() as $act) {
          $task2->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime2())) {
        $task2->setTime($fastTask->getDatum()->modify($fastTask->getTime2()));
      } else {
        $task2->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar2())) {
        $task2->setCar($fastTask->getCar2());
        if (!is_null($task2->getDriver())) {
          $task2->setDriver($fastTask->getDriver2());
        } else {
          $task2->setDriver($fastTask->getGeo12());
        }
      }
      $task2 = $this->saveTaskFromList($task2, $user);
      $fastTask->setTask2($task2->getId());
    }
    }
    if ($fastTask->getTask3() == 0) {
      if(!is_null($fastTask->getProject3())) {
      $task3 = new Task();
      $project3 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject3());
      $task3->setProject($project3);
      $task3->setDatumKreiranja($fastTask->getDatum());
      $task3->setTitle($project3->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task3->setCreatedBy($user);
      $task3->setIsTimeRoundUp(true);
      $task3->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription3())) {
        $task3->setDescription($fastTask->getDescription3());
      }
      if(!empty($fastTask->getOprema3())) {
        foreach ($fastTask->getOprema3() as $opr) {
          $task3->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo13())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo13()));
        $task3->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo13())->getId());
      }
      if(!is_null($fastTask->getGeo23())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo23()));
      }
      if(!is_null($fastTask->getGeo33())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo33()));
      }
      if(!empty($fastTask->getActivity3())) {
        foreach ($fastTask->getActivity3() as $act) {
          $task3->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime3())) {
        $task3->setTime($fastTask->getDatum()->modify($fastTask->getTime3()));
      } else {
        $task3->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar3())) {
        $task3->setCar($fastTask->getCar3());
        if (!is_null($task3->getDriver())) {
          $task3->setDriver($fastTask->getDriver3());
        } else {
          $task3->setDriver($fastTask->getGeo13());
        }
      }
      $task3 = $this->saveTaskFromList($task3, $user);
      $fastTask->setTask3($task3->getId());
    }
    }
    if ($fastTask->getTask4() == 0) {
      if(!is_null($fastTask->getProject4())) {
      $task4 = new Task();
      $project4 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject4());
      $task4->setProject($project4);
      $task4->setDatumKreiranja($fastTask->getDatum());
      $task4->setTitle($project4->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task4->setCreatedBy($user);
      $task4->setIsTimeRoundUp(true);
      $task4->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription4())) {
        $task4->setDescription($fastTask->getDescription4());
      }
      if(!empty($fastTask->getOprema4())) {
        foreach ($fastTask->getOprema4() as $opr) {
          $task4->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo14())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo14()));
        $task4->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo14())->getId());
      }
      if(!is_null($fastTask->getGeo24())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo24()));
      }
      if(!is_null($fastTask->getGeo34())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo34()));
      }
      if(!empty($fastTask->getActivity4())) {
        foreach ($fastTask->getActivity4() as $act) {
          $task4->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime4())) {
        $task4->setTime($fastTask->getDatum()->modify($fastTask->getTime4()));
      } else {
        $task4->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar4())) {
        $task4->setCar($fastTask->getCar4());
        if (!is_null($task4->getDriver())) {
          $task4->setDriver($fastTask->getDriver4());
        } else {
          $task4->setDriver($fastTask->getGeo14());
        }
      }
      $task4 = $this->saveTaskFromList($task4, $user);
      $fastTask->setTask4($task4->getId());
    }
    }
    if ($fastTask->getTask5() == 0) {
      if(!is_null($fastTask->getProject5())) {
      $task5 = new Task();
      $project5 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject5());
      $task5->setProject($project5);
      $task5->setDatumKreiranja($fastTask->getDatum());
      $task5->setTitle($project5->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task5->setCreatedBy($user);
      $task5->setIsTimeRoundUp(true);
      $task5->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription5())) {
        $task5->setDescription($fastTask->getDescription5());
      }
      if(!empty($fastTask->getOprema5())) {
        foreach ($fastTask->getOprema5() as $opr) {
          $task5->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo15())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo15()));
        $task5->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo15())->getId());
      }
      if(!is_null($fastTask->getGeo25())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo25()));
      }
      if(!is_null($fastTask->getGeo35())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo35()));
      }
      if(!empty($fastTask->getActivity5())) {
        foreach ($fastTask->getActivity5() as $act) {
          $task5->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime5())) {
        $task5->setTime($fastTask->getDatum()->modify($fastTask->getTime5()));
      } else {
        $task5->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar5())) {
        $task5->setCar($fastTask->getCar5());
        if (!is_null($task5->getDriver())) {
          $task5->setDriver($fastTask->getDriver5());
        } else {
          $task5->setDriver($fastTask->getGeo15());
        }
      }
      $task5 = $this->saveTaskFromList($task5, $user);
      $fastTask->setTask5($task5->getId());
    }
    }
    if ($fastTask->getTask6() == 0) {
      if(!is_null($fastTask->getProject6())) {
      $task6 = new Task();
      $project6 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject6());
      $task6->setProject($project6);
      $task6->setDatumKreiranja($fastTask->getDatum());
      $task6->setTitle($project6->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task6->setCreatedBy($user);
      $task6->setIsTimeRoundUp(true);
      $task6->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription6())) {
        $task6->setDescription($fastTask->getDescription6());
      }
      if(!empty($fastTask->getOprema6())) {
        foreach ($fastTask->getOprema6() as $opr) {
          $task6->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo16())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo16()));
        $task6->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo16())->getId());
      }
      if(!is_null($fastTask->getGeo26())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo26()));
      }
      if(!is_null($fastTask->getGeo36())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo36()));
      }
      if(!empty($fastTask->getActivity6())) {
        foreach ($fastTask->getActivity6() as $act) {
          $task6->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime6())) {
        $task6->setTime($fastTask->getDatum()->modify($fastTask->getTime6()));
      } else {
        $task6->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar6())) {
        $task6->setCar($fastTask->getCar6());
        if (!is_null($task6->getDriver())) {
          $task6->setDriver($fastTask->getDriver6());
        } else {
          $task6->setDriver($fastTask->getGeo16());
        }
      }
      $task6 = $this->saveTaskFromList($task6, $user);
      $fastTask->setTask6($task6->getId());
    }
    }
    if ($fastTask->getTask7() == 0) {
      if(!is_null($fastTask->getProject7())) {
      $task7 = new Task();
      $project7 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject7());
      $task7->setProject($project7);
      $task7->setDatumKreiranja($fastTask->getDatum());
      $task7->setTitle($project7->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task7->setCreatedBy($user);
      $task7->setIsTimeRoundUp(true);
      $task7->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription7())) {
        $task7->setDescription($fastTask->getDescription7());
      }
      if(!empty($fastTask->getOprema7())) {
        foreach ($fastTask->getOprema7() as $opr) {
          $task7->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo17())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo17()));
        $task7->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo17())->getId());
      }
      if(!is_null($fastTask->getGeo27())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo27()));
      }
      if(!is_null($fastTask->getGeo37())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo37()));
      }
      if(!empty($fastTask->getActivity7())) {
        foreach ($fastTask->getActivity7() as $act) {
          $task7->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime7())) {
        $task7->setTime($fastTask->getDatum()->modify($fastTask->getTime7()));
      } else {
        $task7->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar7())) {
        $task7->setCar($fastTask->getCar7());
        if (!is_null($task7->getDriver())) {
          $task7->setDriver($fastTask->getDriver7());
        } else {
          $task7->setDriver($fastTask->getGeo17());
        }
      }
      $task7 = $this->saveTaskFromList($task7, $user);
      $fastTask->setTask7($task7->getId());
    }
    }
    if ($fastTask->getTask8() == 0) {
      if(!is_null($fastTask->getProject8())) {
      $task8 = new Task();
      $project8 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject8());
      $task8->setProject($project8);
      $task8->setDatumKreiranja($fastTask->getDatum());
      $task8->setTitle($project8->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task8->setCreatedBy($user);
      $task8->setIsTimeRoundUp(true);
      $task8->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription8())) {
        $task8->setDescription($fastTask->getDescription8());
      }
      if(!empty($fastTask->getOprema8())) {
        foreach ($fastTask->getOprema8() as $opr) {
          $task8->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo18())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo18()));
        $task8->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo18())->getId());
      }
      if(!is_null($fastTask->getGeo28())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo28()));
      }
      if(!is_null($fastTask->getGeo38())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo38()));
      }
      if(!empty($fastTask->getActivity8())) {
        foreach ($fastTask->getActivity8() as $act) {
          $task8->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime8())) {
        $task8->setTime($fastTask->getDatum()->modify($fastTask->getTime8()));
      } else {
        $task8->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar8())) {
        $task8->setCar($fastTask->getCar8());
        if (!is_null($task8->getDriver())) {
          $task8->setDriver($fastTask->getDriver8());
        } else {
          $task8->setDriver($fastTask->getGeo18());
        }
      }
      $task8 = $this->saveTaskFromList($task8, $user);
      $fastTask->setTask8($task8->getId());
    }
    }
    if ($fastTask->getTask9() == 0) {
      if(!is_null($fastTask->getProject9())) {
      $task9 = new Task();
      $project9 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject9());
      $task9->setProject($project9);
      $task9->setDatumKreiranja($fastTask->getDatum());
      $task9->setTitle($project9->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task9->setCreatedBy($user);
      $task9->setIsTimeRoundUp(true);
      $task9->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription9())) {
        $task9->setDescription($fastTask->getDescription9());
      }
      if(!empty($fastTask->getOprema9())) {
        foreach ($fastTask->getOprema9() as $opr) {
          $task9->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo19())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo19()));
        $task9->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo19())->getId());
      }
      if(!is_null($fastTask->getGeo29())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo29()));
      }
      if(!is_null($fastTask->getGeo39())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo39()));
      }
      if(!empty($fastTask->getActivity9())) {
        foreach ($fastTask->getActivity9() as $act) {
          $task9->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime9())) {
        $task9->setTime($fastTask->getDatum()->modify($fastTask->getTime9()));
      } else {
        $task9->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar9())) {
        $task9->setCar($fastTask->getCar9());
        if (!is_null($task9->getDriver())) {
          $task9->setDriver($fastTask->getDriver9());
        } else {
          $task9->setDriver($fastTask->getGeo19());
        }
      }
      $task9 = $this->saveTaskFromList($task9, $user);
      $fastTask->setTask9($task9->getId());
    }
    }
    if ($fastTask->getTask10() == 0) {
      if(!is_null($fastTask->getProject10())) {
      $task10 = new Task();
      $project10 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject10());
      $task10->setProject($project10);
      $task10->setDatumKreiranja($fastTask->getDatum());
      $task10->setTitle($project10->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task10->setCreatedBy($user);
      $task10->setIsTimeRoundUp(true);
      $task10->setRoundingInterval(15);

      if(!is_null($fastTask->getDescription10())) {
        $task10->setDescription($fastTask->getDescription10());
      }
      if(!empty($fastTask->getOprema10())) {
        foreach ($fastTask->getOprema10() as $opr) {
          $task10->addOprema($this->getEntityManager()->getRepository(Tool::class)->find($opr));
        }
      }
      if(!is_null($fastTask->getGeo110())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo110()));
        $task10->setPriorityUserLog($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo110())->getId());
      }
      if(!is_null($fastTask->getGeo210())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo210()));
      }
      if(!is_null($fastTask->getGeo310())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo310()));
      }
      if(!empty($fastTask->getActivity10())) {
        foreach ($fastTask->getActivity10() as $act) {
          $task10->addActivity($this->getEntityManager()->getRepository(Activity::class)->find($act));
        }
      }

      if(!is_null($fastTask->getTime10())) {
        $task10->setTime($fastTask->getDatum()->modify($fastTask->getTime10()));
      } else {
        $task10->setTime($fastTask->getDatum());
      }
      if(!is_null($fastTask->getCar10())) {
        $task10->setCar($fastTask->getCar10());
        if (!is_null($task10->getDriver())) {
          $task10->setDriver($fastTask->getDriver10());
        } else {
          $task10->setDriver($fastTask->getGeo110());
        }
      }
      $task10 = $this->saveTaskFromList($task10, $user);
      $fastTask->setTask10($task10->getId());
    }
    }

    return $this->getEntityManager()->getRepository(FastTask::class)->save($fastTask);
  }

  public function save(Task $task): Task {
    if (is_null($task->getId())) {
      $this->getEntityManager()->persist($task);
    }

    $this->getEntityManager()->flush();
    return $task;
  }

  public function remove(int $taskId): void {
    $task = $this->getEntityManager()->getRepository(Task::class)->find($taskId);
    $logs = $task->getTaskLogs();

    foreach ($logs as $log) {
      $task->removeTaskLog($log);
    }
    $this->getEntityManager()->remove($task);
    $this->getEntityManager()->flush();

  }

  public function findForFormProject(Project $project = null): Task {

      $task = new Task();
      $task->setProject($project);
      return $task;

  }

  public function findForForm(int $id = 0): Task {
    if (empty($id)) {
      return new Task();
    }
    return $this->getEntityManager()->getRepository(Task::class)->find($id);

  }

//    /**
//     * @return Task[] Returns an array of Task objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Task
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
