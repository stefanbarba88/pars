<?php

namespace App\Repository;

use App\Entity\Label;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Label>
 *
 * @method Label|null find($id, $lockMode = null, $lockVersion = null)
 * @method Label|null findOneBy(array $criteria, array $orderBy = null)
 * @method Label[]    findAll()
 * @method Label[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabelRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Label::class);
  }

  public function save(Label $label): Label {
    $badge = 'badge bg-' . $label->getColor();
    $label->setLabel('<span class="' . $badge . '">' . $label->getTitle() . '</span>');

    if (is_null($label->getId())) {
      $this->getEntityManager()->persist($label);
    }

    $this->getEntityManager()->flush();
    return $label;
  }

  public function remove(Label $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Label {
    if (empty($id)) {
      return new Label();
    }
    return $this->getEntityManager()->getRepository(Label::class)->find($id);
  }
  public function getLabelsPaginator() {

    return $this->createQueryBuilder('c')
      ->orderBy('c.isSuspended', 'ASC')
      ->orderBy('c.title', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->getQuery();


  }


//    /**
//     * @return Label[] Returns an array of Label objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Label
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
