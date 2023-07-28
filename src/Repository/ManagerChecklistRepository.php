<?php

namespace App\Repository;

use App\Entity\ManagerChecklist;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ManagerChecklist>
 *
 * @method ManagerChecklist|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerChecklist|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerChecklist[]    findAll()
 * @method ManagerChecklist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerChecklistRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ManagerChecklist::class);
  }

  public function save(ManagerChecklist $checklist): ManagerChecklist {

    if (is_null($checklist->getId())) {
      $this->getEntityManager()->persist($checklist);
    }

    $this->getEntityManager()->flush();
    return $checklist;
  }

  public function finish(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setStatus(2);
    $checklist->setFinish(new DateTimeImmutable());

    $this->save($checklist);
    return $checklist;
  }

  public function delete(ManagerChecklist $checklist): ManagerChecklist {

    if ($checklist->getStatus() != 0) {
      $checklist->setStatus(0);
    } else {
      $checklist->setStatus(1);
    }

    $this->save($checklist);
    return $checklist;
  }

  public function remove(ManagerChecklist $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): ManagerChecklist {
    if (empty($id)) {
      return new ManagerChecklist();
    }
    return $this->getEntityManager()->getRepository(ManagerChecklist::class)->find($id);
  }
//    /**
//     * @return ManagerChecklist[] Returns an array of ManagerChecklist objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ManagerChecklist
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
