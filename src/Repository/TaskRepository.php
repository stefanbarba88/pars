<?php

namespace App\Repository;

use App\Classes\Data\FastTaskData;
use App\Classes\Data\TaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Activity;
use App\Entity\FastTask;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskHistory;
use App\Entity\TaskLog;
use App\Entity\User;
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

  public function createTasksFromList(FastTask $fastTask, User $user): FastTask  {

   if(!is_null($fastTask->getProject1())) {
      $task1 = new Task();
      $project1 = $this->getEntityManager()->getRepository(Project::class)->find($fastTask->getProject1());
      $task1->setProject($project1);

      $task1->setTitle($project1->getTitle() . ' - ' . $fastTask->getDatum()->format('d.m.Y'));
      $task1->setIsTimeRoundUp(true);
      $task1->setRoundingInterval(15);
      $task1->setDatumKreiranja($fastTask->getDatum());

      if(!is_null($fastTask->getDescription1())) {
        $task1->setDescription($fastTask->getDescription1());
      }
      if(!is_null($fastTask->getOprema1())) {
        $task1->setOprema($fastTask->getOprema1());
      }
      if(!is_null($fastTask->getGeo11())) {
        $task1->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo11()));
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
      $this->saveTaskFromList($task1, $user);
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
      if(!is_null($fastTask->getOprema2())) {
        $task2->setOprema($fastTask->getOprema2());
      }
      if(!is_null($fastTask->getGeo12())) {
        $task2->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo12()));
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
     $this->saveTaskFromList($task2, $user);
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
      if(!is_null($fastTask->getOprema3())) {
        $task3->setOprema($fastTask->getOprema3());
      }
      if(!is_null($fastTask->getGeo13())) {
        $task3->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo13()));
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
     $this->saveTaskFromList($task3, $user);
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
      if(!is_null($fastTask->getOprema4())) {
        $task4->setOprema($fastTask->getOprema4());
      }
      if(!is_null($fastTask->getGeo14())) {
        $task4->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo14()));
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
     $this->saveTaskFromList($task4, $user);
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
      if(!is_null($fastTask->getOprema5())) {
        $task5->setOprema($fastTask->getOprema5());
      }
      if(!is_null($fastTask->getGeo15())) {
        $task5->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo15()));
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
     $this->saveTaskFromList($task5, $user);
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
      if(!is_null($fastTask->getOprema6())) {
        $task6->setOprema($fastTask->getOprema6());
      }
      if(!is_null($fastTask->getGeo16())) {
        $task6->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo16()));
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
     $this->saveTaskFromList($task6, $user);
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
      if(!is_null($fastTask->getOprema7())) {
        $task7->setOprema($fastTask->getOprema7());
      }
      if(!is_null($fastTask->getGeo17())) {
        $task7->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo17()));
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
     $this->saveTaskFromList($task7, $user);
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
      if(!is_null($fastTask->getOprema8())) {
        $task8->setOprema($fastTask->getOprema8());
      }
      if(!is_null($fastTask->getGeo18())) {
        $task8->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo18()));
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
     $this->saveTaskFromList($task8, $user);
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
      if(!is_null($fastTask->getOprema9())) {
        $task9->setOprema($fastTask->getOprema9());
      }
      if(!is_null($fastTask->getGeo19())) {
        $task9->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo19()));
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
     $this->saveTaskFromList($task9, $user);
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
      if(!is_null($fastTask->getOprema10())) {
        $task10->setOprema($fastTask->getOprema10());
      }
      if(!is_null($fastTask->getGeo110())) {
        $task10->addAssignedUser($this->getEntityManager()->getRepository(User::class)->find($fastTask->getGeo110()));
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
     $this->saveTaskFromList($task10, $user);
    }

   $fastTask->setStatus(FastTaskData::FINAL);
   return $this->getEntityManager()->getRepository(FastTask::class)->save($fastTask);

  }


  public function save(Task $task): Task {
    if (is_null($task->getId())) {
      $this->getEntityManager()->persist($task);
    }

    $this->getEntityManager()->flush();
    return $task;
  }

  public function remove(Task $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
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
