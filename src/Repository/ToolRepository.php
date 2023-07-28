<?php

namespace App\Repository;

use App\Entity\FastTask;
use App\Entity\Tool;
use App\Entity\ToolHistory;
use App\Entity\ToolReservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Tool>
 *
 * @method Tool|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tool|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tool[]    findAll()
 * @method Tool[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Tool::class);
    $this->security = $security;
  }

  public function saveHistory(Tool $tool, ?string $history): Tool {
    $historyTool = new ToolHistory();
    $historyTool->setHistory($history);

    $tool->addToolHistory($historyTool);

    return $tool;
  }

  public function save(Tool $tool, ?string $history = null): Tool {

    if (!is_null($history)) {
      $this->saveHistory($tool, $history);
    }

    if (is_null($tool->getId())) {
      $tool->setCreatedBy($this->security->getUser());
      $this->getEntityManager()->persist($tool);
    } else {
      $tool->setEditBy($this->security->getUser());
    }

    $this->getEntityManager()->flush();

    return $tool;
  }

  public function remove(Tool $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Tool {
    if (empty($id)) {
      return new Tool();
    }
    return $this->getEntityManager()->getRepository(Tool::class)->find($id);
  }

  public function findReservedToolsBy(User $user): array {

    $list = [];

    $reservations = $this->getEntityManager()->getRepository(ToolReservation::class)->findBy(['user' => $user, 'finished' => null], ['id' => 'desc']);

    if (!is_null($reservations)) {
      foreach ($reservations as $res) {
        $tool = $res->getTool();
        $whereToolShouldGo = $this->getEntityManager()->getRepository(FastTask::class)->whereToolShouldGo($tool);
        $list[] = [
          'reservation' => $res,
          'where' => $whereToolShouldGo
        ];
      }
    }

  return $list;

  }


//    /**
//     * @return Tool[] Returns an array of Tool objects
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

//    public function findOneBySomeField($value): ?Tool
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}