<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Comment::class);
  }

  public function save(Comment $comment): Comment {

    if (is_null($comment->getId())) {
      $this->getEntityManager()->persist($comment);
    }

    $this->getEntityManager()->flush();
    return $comment;

  }

  public function remove(Comment $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getCommentsByUser(User $user): array {

    return $this->createQueryBuilder('c')
      ->andWhere('c.user = :userId')
      ->andWhere('c.isSuspended = 0')
      ->setParameter(':userId', $user->getId())
      ->addOrderBy('c.id', 'DESC')
      ->getQuery()
      ->getResult();

  }

  public function findForFormTask(Task $task = null): Comment {

    $comment = new Comment();
    $comment->setTask($task);
    return $comment;

  }

//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
