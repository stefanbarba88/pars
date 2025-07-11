<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Entity\Lamela;
use App\Entity\Projekat;
use App\Entity\Prostorija;
use App\Entity\Sprat;
use App\Entity\Stan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prostorija>
 *
 * @method Prostorija|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prostorija|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prostorija[]    findAll()
 * @method Prostorija[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProstorijaRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Prostorija::class);
    }

    public function save(Prostorija $entity): Prostorija {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function remove(Prostorija $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getStanPovrsina(Stan $stan): float {
        $rez = $this->createQueryBuilder('e')
            ->select('SUM(e.povrsina)')
            ->where('e.povrsina IS NOT NULL')
            ->andWhere('e.stan = :stan')
            ->setParameter('stan', $stan)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) $rez;
    }
    public function getSpratPovrsina(Sprat $sprat): float {
        $povrs = 0;

        $stanovi = $sprat->getStans()->toArray();

        foreach ($stanovi as $stan) {
            $rez = $this->getStanPovrsina($stan);
            $povrs += $rez;
        }

        return (float) $povrs;
    }

    public function getLamelaPovrsina(Lamela $lamela): float {
        $povrs = 0;

        $spratovi = $lamela->getSprats()->toArray();

        foreach ($spratovi as $sprat) {
            $rez = $this->getSpratPovrsina($sprat);
            $povrs += $rez;
        }

        return (float) $povrs;
    }
    public function getProjekatPovrsina(Projekat $projekat): float {
        $povrs = 0;

        $lamele = $projekat->getLamelas()->toArray();

        foreach ($lamele as $lamela) {
            $rez = $this->getLamelaPovrsina($lamela);
            $povrs += $rez;
        }

        return (float) $povrs;
    }


    public function getProstorijeCheck($filter, $user): array {
        $rez =  $this->createQueryBuilder('e')
                ->where('e.isEdit  = 1')
                ->orderBy('e.id', 'ASC')
                ->getQuery()->getResult();

        $prostorije = [];
        if (!empty($filter['projekat'])) {
            foreach ($rez as $prostorija) {
                if ($prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getId() == $filter['projekat']) {
                    $prostorije[] = $prostorija;
                }
            }
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($prostorije as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $prostorije;
        } else {
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($rez as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $rez;
        }
    }
    public function getProstorijePlan($filter, $user): array {
        $rez =  $this->createQueryBuilder('e')
                ->where('e.isPlan  = 1')
                ->orderBy('e.id', 'ASC')
                ->getQuery()->getResult();

        $prostorije = [];
        if (!empty($filter['projekat'])) {
            foreach ($rez as $prostorija) {
                if ($prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getId() == $filter['projekat']) {
                    $prostorije[] = $prostorija;
                }
            }
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($prostorije as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $prostorije;
        } else {
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($rez as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $rez;
        }
    }

    public function getProstorijeMerenje($filter, $user): array {
        $rez =  $this->createQueryBuilder('e')
            ->where('e.unos1 IS NULL')
            ->orderBy('e.id', 'ASC')
            ->getQuery()->getResult();

        $prostorije = [];
        if (!empty($filter['projekat'])) {
            foreach ($rez as $prostorija) {
                if ($prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getId() == $filter['projekat']) {
                    $prostorije[] = $prostorija;
                }
            }
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($prostorije as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $prostorije;
        } else {
            if ($user->getUserType() == UserRolesData::ROLE_EMPLOYEE) {
                $konacno = [];
                foreach ($rez as $prostorija) {
                    if (in_array($user->getId(), (array)$prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getZaposleni())) {
                        $konacno[] = $prostorija;
                    }
                    if (in_array($user, $prostorija->getStan()->getSprat()->getLamela()->getProjekat()->getAssigned()->toArray())) {
                        if (!in_array($prostorija, $konacno)) {
                            $konacno[] = $prostorija;
                        }
                    }
                }
                return $konacno;
            }
            return $rez;
        }


    }

//    /**
//     * @return Prostorija[] Returns an array of Prostorija objects
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

//    public function findOneBySomeField($value): ?Prostorija
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
