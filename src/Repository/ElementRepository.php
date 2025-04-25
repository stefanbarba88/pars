<?php

namespace App\Repository;

use App\Entity\Element;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Element>
 *
 * @method Element|null find($id, $lockMode = null, $lockVersion = null)
 * @method Element|null findOneBy(array $criteria, array $orderBy = null)
 * @method Element[]    findAll()
 * @method Element[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElementRepository extends ServiceEntityRepository {
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security) {
        parent::__construct($registry, Element::class);
        $this->security = $security;
    }

    public function save(Element $element): Element {
        if (is_null($element->getId())) {
            $this->getEntityManager()->persist($element);
        }

        $this->getEntityManager()->flush();
        return $element;
    }

    public function remove(Element $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function searchByTerm(string $term) {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.title, p.productKey') // Include the root entity 'u'
            ->where('p.title LIKE :term')
            ->orWhere('p.productKey LIKE :term')
            ->andWhere('p.isSuspended = 0')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery()
            ->getResult();
    }

    public function getElementsPaginator($company, $filter): QueryBuilder {

        $qb = $this->createQueryBuilder('u');


        $qb->where('u.company = :company')
            ->setParameter(':company', $company);

        if (!empty($filter['key'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.productKey', ':key'),
            ))
                ->setParameter('key', '%' . $filter['key'] . '%');
        }
        if (!empty($filter['title'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('u.title', ':title'),
            ))
                ->setParameter('title', '%' . $filter['title'] . '%');
        }

        $qb
            ->orderBy('u.isSuspended', 'ASC')
            ->addOrderBy('u.title', 'ASC')
            ->getQuery();


        return $qb;

    }

    public function findForForm(int $id = 0): Element {
        if (empty($id)) {
            $label =  new Element();
            $label->setCompany($this->security->getUser()->getCompany());
            return $label;
        }
        return $this->getEntityManager()->getRepository(Element::class)->find($id);
    }
//    /**
//     * @return Element[] Returns an array of Element objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Element
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
