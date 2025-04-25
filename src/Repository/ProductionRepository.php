<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Production;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductionRepository extends ServiceEntityRepository {
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security) {
        parent::__construct($registry, Production::class);
        $this->security = $security;
    }


    public function save(Production $product): Production {

        if (is_null($product->getId())) {
            $this->getEntityManager()->persist($product);
        }

        $this->getEntityManager()->flush();
        return $product;

    }

    public function remove(Production $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function getProductionsPaginator(Company $company, array $filter): Query {

        $qb = $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->setParameter('company', $company);

        if (!empty($filter['project'])) {

            $qb->andWhere('c.project = :project');
            $qb->setParameter('project', $filter['project']);
        }

        if (!empty($filter['key'])) {
            $qb->andWhere(
                $qb->expr()->like('c.productKey', ':productKey')
            )->setParameter('productKey', '%' . $filter['key'] . '%');
        }

        $qb->orderBy('c.isSuspended', 'ASC')
        ->addOrderBy('c.status', 'ASC')
        ->addOrderBy('c.percent', 'DESC');

        return $qb->getQuery();
    }

    public function getProductionByDate(Company $company, $start, $stop): array {

        $qb = $this->createQueryBuilder('p');
        $qb
            ->where('p.created >= :start')
            ->andWhere('p.created < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $stop);

        return $qb->getQuery()->getResult();

    }



    public function findForForm(int $id = 0): Production {
        if (empty($id)) {
            $product =  new Production();
            $product->setCompany($this->security->getUser()->getCompany());
            return $product;
        }
        return $this->getEntityManager()->getRepository(Production::class)->find($id);
    }

    public function generateProductKey(): string {

        $order = $this->getEntityManager()->getRepository(Production::class)->count(['company' => $this->security->getUser()->getCompany(), 'isSuspended' => false]);

        $date = date('Ymd'); // npr: 20250422
        $orderPadded = str_pad($order + 1, 5, '0', STR_PAD_LEFT); // npr: 00001

        // Spajanje u ključ
        $key = "{$date}/{$orderPadded}";

        return $key;
    }

    public function azurirajProgres(array $podaci, array $izmene): array {
        $ukupnoTren = 0;

        foreach ($podaci['progres'] as &$grupa) {
            foreach ($grupa as &$materijali) {
                $product = (int)$materijali['product'];
                $id = (int)$materijali['id'];

                if (isset($izmene[$product]) && is_array($izmene[$product]) && isset($izmene[$product][$id])) {
                    $novaVrednost = (float)$izmene[$product][$id];
                    $materijali['kolicinaTren'] += $novaVrednost; // ⬅ sabiranje umesto zamene
                }

                $ukupnoTren += (float)$materijali['kolicinaTren'];
            }
        }

        $podaci['kolicinaTren'] = $ukupnoTren;

        $podaci['percent'] = $podaci['kolicina'] > 0
            ? round(($ukupnoTren / $podaci['kolicina']) * 100, 2)
            : 0;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Belgrade'));
        $podaci['datum'] = [
            'date' => $now->format('Y-m-d H:i:s.u'),
            'timezone_type' => 3,
            'timezone' => 'Europe/Belgrade'
        ];

        return $podaci;
    }

    public function azurirajProgres1(array $podaci, array $izmene, array $elem): array {
        $ukupnoTren = 0;

        foreach ($podaci['progres'] as &$grupa) {
            foreach ($grupa as &$materijali) {
                $product = (int)$materijali['product'];
                $id = (int)$materijali['id'];

                // Prvo umanjujemo ako postoji prethodna vrednost u $elem
                if (isset($elem[$product][$id]) && is_numeric($elem[$product][$id])) {
                    $materijali['kolicinaTren'] -= (float)$elem[$product][$id];
                    if ($materijali['kolicinaTren'] < 0) {
                        $materijali['kolicinaTren'] = 0; // zaštita od negativnih vrednosti
                    }
                }

                // Zatim dodajemo novu vrednost iz $izmene
                if (isset($izmene[$product][$id]) && is_numeric($izmene[$product][$id])) {
                    $materijali['kolicinaTren'] += (float)$izmene[$product][$id];
                }

                $ukupnoTren += (float)$materijali['kolicinaTren'];
            }
        }

        $podaci['kolicinaTren'] = $ukupnoTren;

        $podaci['percent'] = $podaci['kolicina'] > 0
            ? round(($ukupnoTren / $podaci['kolicina']) * 100, 2)
            : 0;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Belgrade'));
        $podaci['datum'] = [
            'date' => $now->format('Y-m-d H:i:s.u'),
            'timezone_type' => 3,
            'timezone' => 'Europe/Belgrade'
        ];

        return $podaci;
    }

    public function azurirajProgres2(array $podaci, array $elem): array {
        $ukupnoTren = 0;

        foreach ($podaci['progres'] as &$grupa) {
            foreach ($grupa as &$materijali) {
                $product = (int)$materijali['product'];
                $id = (int)$materijali['id'];

                // Prvo umanjujemo ako postoji prethodna vrednost u $elem
                if (isset($elem[$product][$id]) && is_numeric($elem[$product][$id])) {
                    $materijali['kolicinaTren'] -= (float)$elem[$product][$id];
                    if ($materijali['kolicinaTren'] < 0) {
                        $materijali['kolicinaTren'] = 0; // zaštita od negativnih vrednosti
                    }
                }

//                // Zatim dodajemo novu vrednost iz $izmene
//                if (isset($izmene[$product][$id]) && is_numeric($izmene[$product][$id])) {
//                    $materijali['kolicinaTren'] += (float)$izmene[$product][$id];
//                }

                $ukupnoTren += (float)$materijali['kolicinaTren'];
            }
        }

        $podaci['kolicinaTren'] = $ukupnoTren;

        $podaci['percent'] = $podaci['kolicina'] > 0
            ? round(($ukupnoTren / $podaci['kolicina']) * 100, 2)
            : 0;

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Belgrade'));
        $podaci['datum'] = [
            'date' => $now->format('Y-m-d H:i:s.u'),
            'timezone_type' => 3,
            'timezone' => 'Europe/Belgrade'
        ];

        return $podaci;
    }


    public function saberiPoProductu(array $podaci): array {
        $rezultat = [];

        foreach ($podaci as $grupa) {
            foreach ($grupa as $product => $stavke) {
                foreach ($stavke as $id => $vrednost) {
                    if ($vrednost === '' || !is_numeric($vrednost)) {
                        continue;
                    }

                    if (!isset($rezultat[$product][$id])) {
                        $rezultat[$product][$id] = 0;
                    }

                    $rezultat[$product][$id] += (float)$vrednost;
                }
            }
        }

        return $rezultat;
    }

    public function ocistiProgres(array $niz): array
    {
        $rezultat = [];

        // Rekurzivna funkcija koja prolazi kroz sve nivoe
        $extractItems = function ($data) use (&$extractItems, &$rezultat) {
            if (isset($data['datum'], $data['percent'], $data['kolicina'], $data['kolicinaTren'])) {
                $datum = \DateTime::createFromFormat('Y-m-d H:i:s.u', $data['datum']['date']);
                $formatiranDatum = $datum ? $datum->format('d.m.Y \u H:i:s') : $data['datum']['date'];

                $rezultat[] = [
                    'datum' => $formatiranDatum,
                    'percent' => $data['percent'],
                    'kolicina' => $data['kolicina'],
                    'kolicinaTren' => $data['kolicinaTren'],
                ];
            } elseif (is_array($data)) {
                foreach ($data as $v) {
                    $extractItems($v);
                }
            }
        };

        // Obradi istoriju
        if (isset($niz[0])) {
            $extractItems($niz[0]);
        }

        // Dodaj trenutno stanje
        if (isset($niz[1]['datum'])) {
            $datum = \DateTime::createFromFormat('Y-m-d H:i:s.u', $niz[1]['datum']['date']);
            $formatiranDatum = $datum ? $datum->format('d.m.Y \u H:i:s') : $niz[1]['datum']['date'];

            $rezultat[] = [
                'datum' => $formatiranDatum,
                'percent' => $niz[1]['percent'],
                'kolicina' => $niz[1]['kolicina'],
                'kolicinaTren' => $niz[1]['kolicinaTren'],
            ];
        }

        // Sortiraj od najmlađeg ka najstarijem
        usort($rezultat, fn($a, $b) => strtotime($b['datum']) <=> strtotime($a['datum']));

        return $rezultat;
    }



//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
