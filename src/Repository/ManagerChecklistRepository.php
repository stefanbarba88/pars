<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
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


  public function getChecklistPaginator(User $loggedUser) {

    return match ($loggedUser->getUserType()) {
      UserRolesData::ROLE_SUPER_ADMIN => $this->createQueryBuilder('c')
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->addOrderBy('c.created', 'DESC')
        ->getQuery(),

      default => $this->createQueryBuilder('c')
        ->andWhere('c.createdBy = :user')
        ->setParameter(':user', $loggedUser)
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->addOrderBy('c.created', 'DESC')
        ->getQuery(),

    };

  }

  public function getChecklistToDoPaginator(User $loggedUser) {

    return  $this->createQueryBuilder('c')
        ->andWhere('c.user = :user')
        ->setParameter(':user', $loggedUser)
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->addOrderBy('c.created', 'DESC')
        ->getQuery();

  }


  public function finish(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setStatus(1);
    $checklist->setFinish(new DateTimeImmutable());

    $this->save($checklist);
    return $checklist;
  }

  public function start(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setStatus(0);
    $checklist->setFinish(null);

    $this->save($checklist);
    return $checklist;
  }

  public function delete(ManagerChecklist $checklist): ManagerChecklist {

    $checklist->setStatus(2);


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


  public function findForFormUser(User $user = null): ManagerChecklist {

    $task = new ManagerChecklist();
    $task->setUser($user);
    return $task;

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
