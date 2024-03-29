<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Notes;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notes>
 *
 * @method Notes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notes[]    findAll()
 * @method Notes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotesRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Notes::class);
  }

  public function save(Notes $notes): Notes {

    if (is_null($notes->getId())) {
      $this->getEntityManager()->persist($notes);
    }

    $this->getEntityManager()->flush();
    return $notes;

  }

  public function remove(Notes $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Notes {
    if (empty($id)) {
      return new Notes();
    }
    return $this->getEntityManager()->getRepository(Notes::class)->find($id);

  }

  public function getNotesByUserPaginator(User $loggedUser) {

    return $this->createQueryBuilder('c')
        ->where('c.isSuspended = 0')
        ->andWhere('c.user = :user')
        ->setParameter(':user', $loggedUser)
        ->orderBy('c.created', 'DESC')
        ->getQuery();


  }

//    /**
//     * @return Notes[] Returns an array of Notes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notes
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
