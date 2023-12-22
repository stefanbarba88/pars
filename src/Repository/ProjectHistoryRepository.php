<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\ProjectHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectHistory>
 *
 * @method ProjectHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectHistory[]    findAll()
 * @method ProjectHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ProjectHistory::class);
  }

  public function save(ProjectHistory $project): ProjectHistory {
    if (is_null($project->getId())) {
      $this->getEntityManager()->persist($project);
    }

    $this->getEntityManager()->flush();
    return $project;
  }

  public function remove(ProjectHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getAllPaginator(Project $project) {

    return $this->createQueryBuilder('u')
      ->andWhere('u.project = :project')
      ->setParameter(':project', $project)
      ->addOrderBy('u.id', 'DESC')
      ->getQuery();
  }


//    /**
//     * @return ProjectHistory[] Returns an array of ProjectHistory objects
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

//    public function findOneBySomeField($value): ?ProjectHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
