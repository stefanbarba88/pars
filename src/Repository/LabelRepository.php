<?php

namespace App\Repository;

use App\Entity\Label;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Label>
 *
 * @method Label|null find($id, $lockMode = null, $lockVersion = null)
 * @method Label|null findOneBy(array $criteria, array $orderBy = null)
 * @method Label[]    findAll()
 * @method Label[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LabelRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Label::class);
    $this->security = $security;
  }

  public function save(Label $label): Label {
    $badge = 'badge bg-' . $label->getColor();

    if ($label->getColor() != 'secondary') {
      $textColor = 'text-white';
    } else {
      $textColor = 'text-primary';
    }

    $label->setLabel('<span class="me-2 ' . $badge . ' ' . $textColor . '">' . $label->getTitle() . '</span>');

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
      $label =  new Label();
      $label->setCompany($this->security->getUser()->getCompany());
      return $label;
    }
    return $this->getEntityManager()->getRepository(Label::class)->find($id);
  }
  public function getLabelsPaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('c')
      ->where('c.company = :company')
      ->setParameter('company', $company)
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
