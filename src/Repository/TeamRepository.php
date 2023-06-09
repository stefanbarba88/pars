<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDO;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Team::class);
  }

  public function save(Team $team): Team {

    if (is_null($team->getId())) {
      $this->getEntityManager()->persist($team);
    }

    $this->getEntityManager()->flush();
    return $team;

  }

  public function remove(Team $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function countTeams(): int {
    $qb = $this->createQueryBuilder('c');

    $qb->select($qb->expr()->count('c'))
      ->andWhere('c.isDeleted = :isDeleted')
      ->setParameter(':isDeleted', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function countTeamsActive(): int {


    $db = $this->getEntityManager()->getConnection();
    $sql = "SELECT teams.id  
            FROM teams LEFT JOIN project_team ON teams.id = project_team.team_id 
            WHERE project_team.team_id IS NOT NULL AND teams.is_deleted = 0";

    $query = $db->prepare($sql);
    return $query->executeStatement();

//
//
//
//    $teams = $this->getEntityManager()->getRepository(Team::class)->findBy(['isDeleted' => false]);
//    $count = 0;
//    foreach ($teams as $team) {
//      if (!is_null($team->getProjects())) {
//        $count++;
//      }
//    }
//  return $count;
  }

  public function countTeamsInactive(): int {

    $db = $this->getEntityManager()->getConnection();
    $sql = "SELECT teams.id  
            FROM teams LEFT JOIN project_team ON teams.id = project_team.team_id 
            WHERE project_team.team_id IS NULL AND teams.is_deleted = 0";

    $query = $db->prepare($sql);
    return $query->executeStatement();
  }

  public function getTeams(int $type): array {

    $teamList = [];
    $db = $this->getEntityManager()->getConnection();
    $sql = match ($type) {
      1 => "SELECT teams.id  
            FROM teams LEFT JOIN project_team ON teams.id = project_team.team_id 
            WHERE project_team.team_id IS NOT NULL",
      2 => "SELECT teams.id
            FROM teams LEFT JOIN project_team ON teams.id = project_team.team_id 
            WHERE project_team.team_id IS NULL",
      default => "SELECT teams.id FROM teams",
    };

    $query = $db->prepare($sql);
    $result = $query->executeQuery()->fetchAllNumeric();

    foreach ($result as $rs) {
      $team = $this->getEntityManager()->getRepository(Team::class)->find($rs[0]);
      $teamList [] = [
        'id' => $team->getId(),
        'naziv' => $team->getTitle(),
        'projekat' => $team->getProjects(),
        'clanovi' => $team->getMember(),
        'status' => $team->getIsDeleted(),
        'kreiran' => $team->getCreated()
      ];
    }
  return $teamList;
  }

  public function findForForm(int $id = 0): Team {
    if (empty($id)) {
      return new Team();
    }
    return $this->getEntityManager()->getRepository(Team::class)->find($id);
  }

//    /**
//     * @return Team[] Returns an array of Team objects
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

//    public function findOneBySomeField($value): ?Team
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
