<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\ClientHistory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientHistory>
 *
 * @method ClientHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientHistory[]    findAll()
 * @method ClientHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientHistoryRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, ClientHistory::class);
  }

  public function save(ClientHistory $client): ClientHistory {
    if (is_null($client->getId())) {
      $this->getEntityManager()->persist($client);
    }

    $this->getEntityManager()->flush();
    return $client;
  }

  public function remove(ClientHistory $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function getAllPaginator(Client $client) {

    return $this->createQueryBuilder('u')
      ->andWhere('u.client = :client')
      ->setParameter(':client', $client)
      ->addOrderBy('u.id', 'DESC')
      ->getQuery();
  }


//    /**
//     * @return ClientHistory[] Returns an array of ClientHistory objects
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

//    public function findOneBySomeField($value): ?ClientHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
