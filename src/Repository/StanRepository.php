<?php

namespace App\Repository;

use App\Entity\Stan;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stan>
 *
 * @method Stan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stan[]    findAll()
 * @method Stan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StanRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Stan::class);
    }

    public function save(Stan $Stan): Stan {
        if (is_null($Stan->getId())) {
            $this->getEntityManager()->persist($Stan);
        }

        $this->getEntityManager()->flush();
        return $Stan;
    }

    public function remove(Stan $entity): void {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

    }

    public function findForForm(int $id = 0): Stan {
        if (empty($id)) {
            return new Stan();
        }
        return $this->getEntityManager()->getRepository(Stan::class)->find($id);
    }

    public function getDeadlineCounter(int $id): array {

        $Stan = $this->getEntityManager()->getRepository(Stan::class)->find($id);

        $poruka = '';
        $klasa = '';
        $klasa1 = '';
        $now = new DateTimeImmutable();
        $now->setTime(0,0);

        if (!is_null($Stan->getDeadline())) {
            $endDate = $Stan->getDeadline();
            // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
            $days = (int) $now->diff($endDate)->format('%R%a');

            if ($days > 0 && $days < 15 && $Stan->getPercent() < 100) {
                $poruka = 'Rok za predaju je za ' . $days . ' dana.';
                $klasa = 'bg-warning bg-opacity-25';
                $klasa1 = 'alert alert-warning fade show';
            } elseif ($days == 0 && $Stan->getPercent() < 100) {
                $poruka = 'Rok za predaju je danas.';
                $klasa = 'bg-danger bg-opacity-25';
                $klasa1 = 'alert alert-danger fade show';
            } elseif ($days < 0 && $Stan->getPercent() < 100) {
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
//     * @return Stan[] Returns an array of Stan objects
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

//    public function findOneBySomeField($value): ?Stan
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
