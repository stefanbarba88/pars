<?php

namespace App\Repository;

use App\Entity\Sprat;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sprat>
 *
 * @method Sprat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sprat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sprat[]    findAll()
 * @method Sprat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpratRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Sprat::class);
    }

    public function save(Sprat $Sprat): Sprat {
        if (is_null($Sprat->getId())) {
            $this->getEntityManager()->persist($Sprat);
        }

        $this->getEntityManager()->flush();
        return $Sprat;
    }

    public function remove(Sprat $entity): void {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function findForForm(int $id = 0): Sprat {
        if (empty($id)) {
            return new Sprat();
        }
        return $this->getEntityManager()->getRepository(Sprat::class)->find($id);
    }

    public function getDeadlineCounter(int $id): array {

        $Sprat = $this->getEntityManager()->getRepository(Sprat::class)->find($id);

        $poruka = '';
        $klasa = '';
        $klasa1 = '';
        $now = new DateTimeImmutable();
        $now->setTime(0,0);

        if (!is_null($Sprat->getDeadline())) {
            $endDate = $Sprat->getDeadline();
            // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
            $days = (int) $now->diff($endDate)->format('%R%a');

            if ($days > 0 && $days < 15 && $Sprat->getPercent() < 100) {
                $poruka = 'Rok za predaju je za ' . $days . ' dana.';
                $klasa = 'bg-warning bg-opacity-25';
                $klasa1 = 'alert alert-warning fade show';
            } elseif ($days == 0 && $Sprat->getPercent() < 100) {
                $poruka = 'Rok za predaju je danas.';
                $klasa = 'bg-danger bg-opacity-25';
                $klasa1 = 'alert alert-danger fade show';
            } elseif ($days < 0 && $Sprat->getPercent() < 100) {
                $poruka = 'Rok za predaju je istekao pre ' . abs($days) . ' dana.';
                $klasa = 'bg-danger bg-opacity-50';
                $klasa1 = 'alert alert-danger fade show';
            }
        }

        return [
            'klasa' => $klasa,
            'poruka' => $poruka,
            'klasa1' => $klasa1,
        ];
    }

//    /**
//     * @return Sprat[] Returns an array of Sprat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sprat
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
