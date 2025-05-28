<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Projekat;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projekat>
 *
 * @method Projekat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projekat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projekat[]    findAll()
 * @method Projekat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjekatRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Projekat::class);
    }

    public function save(Projekat $projekat): Projekat {
        if (is_null($projekat->getId())) {
            $this->getEntityManager()->persist($projekat);
        }

        $this->getEntityManager()->flush();
        return $projekat;
    }

    public function remove(Projekat $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findForForm(int $id = 0): Projekat {
        if (empty($id)) {
            return new Projekat();
        }
        return $this->getEntityManager()->getRepository(Projekat::class)->find($id);
    }

    public function getDeadlineCounter(int $id): array {

        $projekat = $this->getEntityManager()->getRepository(Projekat::class)->find($id);

        $poruka = '';
        $klasa = '';
        $klasa1 = '';
        $now = new DateTimeImmutable();
        $now->setTime(0,0);

        if (!is_null($projekat->getDeadline())) {
            $endDate = $projekat->getDeadline();
            // Izračunavanje razlike između trenutnog datuma i datuma kraja ugovora
            $days = (int) $now->diff($endDate)->format('%R%a');

            if ($days > 0 && $days < 15 && $projekat->getPercent() < 100) {
                $poruka = 'Rok za predaju je za ' . $days . ' dana.';
                $klasa = 'bg-warning bg-opacity-25';
                $klasa1 = 'alert alert-warning fade show';
            } elseif ($days == 0 && $projekat->getPercent() < 100) {
                $poruka = 'Rok za predaju je danas.';
                $klasa = 'bg-danger bg-opacity-25';
                $klasa1 = 'alert alert-danger fade show';
            } elseif ($days < 0 && $projekat->getPercent() < 100) {
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

    public function getProjektiByUserPaginator(User $user, $filter) {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder->addSelect(
            'CASE WHEN c.percent = 100 THEN 1 ELSE 0 END AS HIDDEN status_sort' // Sortiranje statusa (4 ide na kraj)
        );
        if (!empty($filter['title'])) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('c.title', ':title'),
            ))
                ->setParameter('title', '%' . $filter['title'] . '%');
        }

        $queryBuilder
            ->orderBy('status_sort', 'ASC')        // Prvo sortiranje po status_sort
            ->addOrderBy('c.deadline', 'ASC');    // Na kraju sortiranje po prioritetu

        $rezultat =  $queryBuilder->getQuery()->getResult();
        if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
            $konacno = [];
            foreach ($rezultat as $projekat) {
                if (in_array($user->getId(), (array)$projekat->getZaposleni())) {
                    $konacno[] = $projekat;
                }
                if (in_array($user, $projekat->getAssigned()->toArray())) {
                    if (!in_array($projekat, $konacno)) {
                        $konacno[] = $projekat;
                    }
                }
            }
            return $konacno;
        }
        return $rezultat;


    }
//    /**
//     * @return Projekat[] Returns an array of Projekat objects
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

//    public function findOneBySomeField($value): ?Projekat
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
