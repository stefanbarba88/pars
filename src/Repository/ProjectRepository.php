<?php

namespace App\Repository;

use App\Classes\Data\VrstaPlacanjaData;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
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

  public function getAllProjects() {

    return $this->createQueryBuilder('p')
      ->addOrderBy('p.isSuspended', 'ASC')
      ->getQuery()
      ->getResult();

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
    } else if ($project->getPayment() == VrstaPlacanjaData::FIKSNA_CENA) {
      $project->setPricePerHour(null);
      $project->setPricePerTask(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_SATU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
    } else {
      $project->setPricePerHour(null);
      $project->setPrice(null);
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
