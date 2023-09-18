<?php

namespace App\Repository;

use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Project::class);
  }

  public function saveProject(Project $project, User $user, ?string $history): Project  {

    $project = $this->paymentTimeSet($project);

    if (!is_null($project->getId())) {

      $historyProject = new ProjectHistory();
      $historyProject->setHistory($history);

      $project->addProjectHistory($historyProject);
      $project->setEditBy($user);

      return $this->save($project);
    }

    $project->setCreatedBy($user);

    return $this->save($project);

  }
  public function getProjectsByUser(User $user) {

    return $this->createQueryBuilder('p')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->andWhere('tl.user = :userId')
      ->setParameter(':userId', $user->getId())
      ->addOrderBy('p.isSuspended', 'ASC')
      ->getQuery()
      ->getResult();

  }
  public function getAllProjects(): array {
    return $this->createQueryBuilder('p')
      ->addOrderBy('p.isSuspended', 'ASC')
      ->getQuery()
      ->getResult();
  }
  public function getAllProjectsPermanent(): array {

    $projects = $this->createQueryBuilder('p')
      ->andWhere('p.isSuspended = 0')
      ->andWhere('p.type <> 2')
      ->addOrderBy('p.isSuspended', 'ASC')
      ->getQuery()
      ->getResult();

    return $projects;
  }
  public function getAllProjectsChange(): array {

    $projects = $this->createQueryBuilder('p')
      ->andWhere('p.isSuspended = 0')
      ->andWhere('p.type <> 1')
      ->addOrderBy('p.isSuspended', 'ASC')
      ->getQuery()
      ->getResult();

    return $projects;
  }
  public function countProjectsChange(): int {
    return $this->createQueryBuilder('p')
      ->select('count(p.id)')
      ->andWhere('p.isSuspended = 0 ')
      ->andWhere('p.type <> 1')
      ->getQuery()
      ->getSingleScalarResult();

  }

  public function countProjectsPermanent(): int {
    return $this->createQueryBuilder('p')
      ->select('count(p.id)')
      ->andWhere('p.isSuspended = 0 ')
      ->andWhere('p.type <> 2')
      ->getQuery()
      ->getSingleScalarResult();

  }
  public function countProjectsActive(): int {

    return $this->createQueryBuilder('p')
      ->select('count(p.id)')
      ->andWhere('p.isSuspended = 0')
      ->getQuery()
      ->getSingleScalarResult();

  }

  public function getImagesByProject(Project $project): array {

    return $this->createQueryBuilder('p')
      ->select('i.thumbnail100', 'i.thumbnail500', 'i.thumbnail1024')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Image::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('p.id = :projectId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();

  }
  public function getPdfsByProject(Project $project): array {

    $pdfs = $this->createQueryBuilder('p')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('p.id = :projectId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();


    $pdfsProject = $this->createQueryBuilder('p')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 'p = i.project')
      ->andWhere('p.id = :projectId')
      ->andWhere('i.stopwatchTime IS NULL')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();

    return array_merge($pdfs, $pdfsProject);

  }
  public function save(Project $project): Project {
    if (is_null($project->getId())) {
      $this->getEntityManager()->persist($project);
    }

    $this->getEntityManager()->flush();
    return $project;
  }
  public function remove(Project $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
  public function paymentTimeSet(Project $project): Project {

    if ($project->getPayment() == VrstaPlacanjaData::BESPLATNO) {
      $project->setPrice(null);
      $project->setPricePerHour(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::FIKSNA_CENA) {
      $project->setPricePerHour(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_SATU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_DANU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerHour(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_MESECU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerHour(null);
    } else {
      $project->setPricePerHour(null);
      $project->setPrice(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    }

    if (!$project->isTimeRoundUp()) {
      $project->setRoundingInterval(null);
      $project->setMinEntry(null);
    }
    return $project;
  }
  public function findForForm(int $id = 0): Project {
    if (empty($id)) {
      return new Project();
    }
    return $this->getEntityManager()->getRepository(Project::class)->find($id);
  }




  public function getReport(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $project = $this->getEntityManager()->getRepository(Project::class)->find($data['project']);

    if (isset($data['category'])){
      foreach ($data['category'] as $cat) {
        $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
      }
    } else {
      $kategorija [] = 0;
    }

    if (isset($data['naplativ'])) {
      $naplativ = $data['naplativ'];
      return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $kategorija, $naplativ);
    }

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $kategorija);
  }


//    /**
//     * @return Project[] Returns an array of Project objects
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

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
