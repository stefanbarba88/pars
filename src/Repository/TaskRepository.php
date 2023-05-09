<?php

namespace App\Repository;

use App\Classes\Data\TaskStatusData;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskHistory;
use App\Entity\TaskLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    $stopwatches = $this->createQueryBuilder('t')
      ->select('s.diff', 's.start', 's.stop')
      ->from(TaskLog::class, 'tl')
      ->from(StopwatchTime::class, 's' )
      ->andWhere('t.id = tl.task')
      ->andWhere('t.id = :taskId')
      ->setParameter(':taskId', $task->getId())
      ->addOrderBy('s.created', 'DESC')
      ->setMaxResults(1)
      ->getQuery()
      ->getResult();


    foreach ($stopwatches as $stopwatch) {
      if (is_null($stopwatch['start']) && is_null($stopwatch['stop']) && is_null($stopwatch['diff'])) {
        return TaskStatusData::NIJE_ZAPOCETO;
      }
      if (!is_null($stopwatch['start']) && is_null($stopwatch['stop']) ) {
       return TaskStatusData::ZAPOCETO;
      }
      if (!is_null($stopwatch['diff'])) {
        return TaskStatusData::ZAVRSENO;
      }
    }

    return TaskStatusData::ZAVRSENO;
  }

  public function getPdfsByTask(Task $task): array {

    return $this->createQueryBuilder('t')
      ->select('i.title', 'i.path')
      ->from(TaskLog::class, 'tl')
      ->from(StopwatchTime::class, 's' )
      ->from(Pdf::class, 'i' )
      ->andWhere('t.id = tl.task')
      ->andWhere('s.id = i.stopwatchTime')
      ->andWhere('t.id = :taskId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

  }

  public function getImagesByTask(Task $task): array {

    return $this->createQueryBuilder('t')
      ->select('i.thumbnail100', 'i.thumbnail500', 'i.thumbnail1024')
      ->from(TaskLog::class, 'tl')
      ->from(StopwatchTime::class, 's' )
      ->from(Image::class, 'i' )
      ->andWhere('t.id = tl.task')
      ->andWhere('s.id = i.stopwatchTime')
      ->andWhere('t.id = :taskId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':taskId', $task->getId())
      ->getQuery()
      ->getResult();

  }

  public function saveTask(Task $task, User $user, ?string $history): Task  {

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

  public function findForFormProject(Project $project = null, int $id = 0): Task {
    if (empty($id)) {
      $task = new Task();
      $task->setProject($project);
      return $task;
    }
    return $this->getEntityManager()->getRepository(Task::class)->find($id);

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
