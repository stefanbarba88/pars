<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

  public function saveProject(Project $project): Project  {

//    if (!is_null($project->getId())) {
//      $this->getEntityManager()->detach($project);
//      $projectHistory = clone $project;
//
//      $history = new ProjectHistory();
//
//      $history->setProject($project);
//      $history->setHistory(json_encode($projectHistory);
////      $this->getEntityManager()->persist($history);
//
//      $history = $this->getEntityManager()->getRepository(ProjectHistory::class)->save($history);
//      dd($history);
//      return $this->save($project);
//    }

    return $this->save($project);

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
