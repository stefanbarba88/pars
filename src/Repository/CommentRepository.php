<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Comment;
use App\Entity\ManagerChecklist;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Comment::class);
    $this->security = $security;
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


  public function getCommentsByUserPaginator($korisnik, $user): Query {
    $company = $korisnik->getCompany();

    if (is_null($user)) {
      return $this->createQueryBuilder('c')
        ->andWhere('c.company = :company')
        ->setParameter('company', $company)
        ->orderBy('c.isSuspended', 'ASC')
        ->addOrderBy('c.id', 'DESC')
        ->getQuery();
    }

      return $this->createQueryBuilder('c')
        ->where('c.user = :userId')
        ->setParameter(':userId', $user->getId())
        ->orderBy('c.isSuspended', 'ASC')
        ->addOrderBy('c.id', 'DESC')
        ->getQuery();

  }

  public function countCommentsActive(): int {
    $company = $this->security->getUser()->getCompany();
    $qb = $this->createQueryBuilder('c');

    $qb->select($qb->expr()->count('c'))
      ->andWhere('c.isSuspended = :isSuspended')
      ->andWhere('c.company = :company')
      ->setParameter(':company', $company)
      ->setParameter(':isSuspended', 0);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function findForFormTask(Task $task = null): Comment {

    $comment = new Comment();
    $comment->setTask($task);
    $comment->setCompany($task->getCompany());
    return $comment;

  }

  public function findForFormTaskInt(ManagerChecklist $task = null): Comment {

    $comment = new Comment();
    $comment->setManagerChecklist($task);
    $comment->setCompany($task->getCompany());
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
