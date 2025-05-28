<?php

namespace App\Repository;

use App\Entity\Lamela;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lamela>
 *
 * @method Lamela|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lamela|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lamela[]    findAll()
 * @method Lamela[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LamelaRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Lamela::class);
    }

    public function save(Lamela $Lamela): Lamela {
        if (is_null($Lamela->getId())) {
            $this->getEntityManager()->persist($Lamela);
        }

        $this->getEntityManager()->flush();
        return $Lamela;
    }

    public function remove(Lamela $entity): void {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

    }

    public function findForForm(int $id = 0): Lamela {
        if (empty($id)) {
            return new Lamela();
        }
        return $this->getEntityManager()->getRepository(Lamela::class)->find($id);
    }

    public function getDeadlineCounter(int $id): array {

        $Lamela = $this->getEntityManager()->getRepository(Lamela::class)->find($id);

        $poruka = '';
        $klasa = '';
        $klasa1 = '';
        $now = new DateTimeImmutable();
        $now->setTime(0,0);

        if (!is_null($Lamela->getDeadline())) {
            $endDate = $Lamela->getDeadline();
            // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
            $days = (int) $now->diff($endDate)->format('%R%a');

            if ($days > 0 && $days < 15 && $Lamela->getPercent() < 100) {
                $poruka = 'Rok za predaju je za ' . $days . ' dana.';
                $klasa = 'bg-warning bg-opacity-25';
                $klasa1 = 'alert alert-warning fade show';
            } elseif ($days == 0 && $Lamela->getPercent() < 100) {
                $poruka = 'Rok za predaju je danas.';
                $klasa = 'bg-danger bg-opacity-25';
                $klasa1 = 'alert alert-danger fade show';
            } elseif ($days < 0 && $Lamela->getPercent() < 100) {
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
//     * @return Lamela[] Returns an array of Lamela objects
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

//    public function findOneBySomeField($value): ?Lamela
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
