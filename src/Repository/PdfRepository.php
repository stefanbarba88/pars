<?php

namespace App\Repository;

use App\Classes\DTO\UploadedFileDTO;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pdf>
 *
 * @method Pdf|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pdf|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pdf[]    findAll()
 * @method Pdf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PdfRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Pdf::class);
  }

  public function save(Pdf $pdf, UploadedFileDTO $file): Pdf {

    $pdf->setTitle($file->getFileName());
    $pdf->setPath($file->getUrl());

    if (is_null($pdf->getId())) {
      $this->getEntityManager()->persist($pdf);
    }

    $this->getEntityManager()->flush();
    return $pdf;
  }

  public function savePdf(Pdf $pdf): Pdf {

    if (is_null($pdf->getId())) {
      $this->getEntityManager()->persist($pdf);
    }

    $this->getEntityManager()->flush();
    return $pdf;
  }


  public function remove(Pdf $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }


//    /**
//     * @return Pdf[] Returns an array of Pdf objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pdf
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
