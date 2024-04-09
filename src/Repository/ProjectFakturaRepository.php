<?php

namespace App\Repository;

use App\Entity\Availability;
use App\Entity\Project;
use App\Entity\ProjectFaktura;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectFaktura>
 *
 * @method ProjectFaktura|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectFaktura|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectFaktura[]    findAll()
 * @method ProjectFaktura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectFakturaRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ProjectFaktura::class);
  }

  public function save(ProjectFaktura $entity): void {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();
  }

  public function remove(ProjectFaktura $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getAllFakturePaginator($filter, $user) {
    $company = $user->getCompany();
    if (!is_null($filter['period'])) {
      $datum = $filter['period'];
      $dateParts = explode('.', $datum);
      $dateImmutable = new DateTimeImmutable($dateParts[1] . '-' . $dateParts[0] . '-01');
    } else {
      $sad = new DateTimeImmutable('first day of last month');
      $dateImmutable = $sad->setTime(0,0);
    }


    $qb = $this->createQueryBuilder('pf');
    $qb
      ->leftJoin(Project::class, 'proj', Join::WITH, 'pf.project = proj.id') // Dodajemo vezu sa entitetom Project
      ->andWhere('pf.company = :company')
      ->andWhere('pf.datum = :datum')
      ->setParameter(':datum', $dateImmutable)
      ->setParameter(':company', $company);

    // Dodajemo sortiranje po naslovu projekta
    $qb->addOrderBy('proj.title', 'ASC');

    $qb
      ->orderBy('pf.status', 'ASC')
      ->addOrderBy('pf.noTasks', 'DESC')
      ->getQuery();

    return $qb;
  }

//    /**
//     * @return ProjectFaktura[] Returns an array of ProjectFaktura objects
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

//    public function findOneBySomeField($value): ?ProjectFaktura
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
