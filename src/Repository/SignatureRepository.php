<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Label;
use App\Entity\Project;
use App\Entity\Signature;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Signature>
 *
 * @method Signature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Signature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Signature[]    findAll()
 * @method Signature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SignatureRepository extends ServiceEntityRepository {
  private Security $security;

  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Signature::class);
    $this->security = $security;
  }

  public function save(Signature $entity): void {
    $this->getEntityManager()->persist($entity);
    $this->getEntityManager()->flush();
  }

  public function remove(Signature $entity): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();
  }


  public function getSignaturesPaginator(User $user): Query {

    if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
      return $this->createQueryBuilder('c')
        ->andWhere('c.employee = :user')
        ->setParameter(':user', $user)
        ->orderBy('c.isApproved', 'ASC')
        ->addOrderBy('c.id', 'ASC')
        ->getQuery();
    }

    return $this->createQueryBuilder('c')
      ->orderBy('c.isApproved', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();

  }

  public function getSignaturesProjectPaginator(Project $project): Query {

    return $this->createQueryBuilder('c')
      ->where('c.relation = :project')
      ->setParameter(':project', $project)
      ->orderBy('c.isApproved', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();


  }

  public function getCountSignatures(Project $project): array {
    $approved = $this->getEntityManager()->getRepository(Signature::class)->findBy(['relation' => $project, 'isApproved' => true]);
    $total = $this->getEntityManager()->getRepository(Signature::class)->findBy(['relation' => $project]);

    return [
      'odobren' => count($approved),
      'ukupno' => count($total)
    ];
  }

  public function getStatusByUserProject(User $user, Project $project): bool {
    if ($project->isElaboratSigned()) {
      $signature = $this->getEntityManager()->getRepository(Signature::class)->findOneBy(['employee' => $user, 'relation' => $project, 'isApproved' => true]);
      if (!is_null($signature)) {
        return true;
      }
      return false;
    }

    return true;
  }

  public function findForForm(int $id = 0): Signature {
    if (empty($id)) {
      return new Signature();
    }
    return $this->getEntityManager()->getRepository(Signature::class)->find($id);
  }

//    /**
//     * @return Signature[] Returns an array of Signature objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Signature
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
