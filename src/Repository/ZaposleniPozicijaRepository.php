<?php

namespace App\Repository;

use App\Entity\ZaposleniPozicija;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<ZaposleniPozicija>
 *
 * @method ZaposleniPozicija|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZaposleniPozicija|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZaposleniPozicija[]    findAll()
 * @method ZaposleniPozicija[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZaposleniPozicijaRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, ZaposleniPozicija::class);
    $this->security = $security;
  }

  public function save(ZaposleniPozicija $pozicija): ZaposleniPozicija {
    $pozicija->setTitleShort(mb_strtoupper($pozicija->getTitleShort()));
    if (is_null($pozicija->getId())) {
      $this->getEntityManager()->persist($pozicija);
    }

    $this->getEntityManager()->flush();
    return $pozicija;
  }

  public function remove(ZaposleniPozicija $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): ZaposleniPozicija {
    if (empty($id)) {
      $pos =  new ZaposleniPozicija();
      $pos->setCompany($this->security->getUser()->getCompany());
      return $pos;
    }
    return $this->getEntityManager()->getRepository(ZaposleniPozicija::class)->find($id);
  }

  public function getPositionsPaginator() {
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
//     * @return ZaposleniPozicija[] Returns an array of ZaposleniPozicija objects
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

//    public function findOneBySomeField($value): ?ZaposleniPozicija
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
