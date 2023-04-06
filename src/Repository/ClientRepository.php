<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\ClientHistory;
use App\Entity\Image;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository {

  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Client::class);
    $this->security = $security;
  }

  public function saveHistory(Client $client, ?string $history): Client {
    $historyClient = new ClientHistory();
    $historyClient->setHistory($history);

    $client->addClientHistory($historyClient);

    return $client;
  }

  public function save(Client $client, ?string $history = null): Client {

    if (!is_null($history)) {
      $this->saveHistory($client, $history);
    }

    if (is_null($client->getId())) {
      //    $user->setEditBy($this->security->getUser());
      $client->setCreatedBy($this->getEntityManager()->getRepository(User::class)->find(1));
      $this->getEntityManager()->getRepository(Image::class)->addImagesClient($client);
      $this->getEntityManager()->persist($client);
    } else {
      $client->setEditBy($this->getEntityManager()->getRepository(User::class)->find(2));
    }

    $this->getEntityManager()->flush();
    return $client;
  }

  public function remove(Client $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForForm(int $id = 0): Client {
    if (empty($id)) {
      return new Client();
    }
    return $this->getEntityManager()->getRepository(Client::class)->find($id);
  }
  public function suspend(Client $client): Client {

    $user = $this->getEntityManager()->getRepository(User::class)->find($client->getKontakt()->getId());
    if ($client->isSuspended()) {
      $user->setIsSuspended(true);
    } else {
      $user->setIsSuspended(false);
    }

    $this->getEntityManager()->getRepository(User::class)->suspend($user);
    $this->save($client);

    return $client;

  }
//    /**
//     * @return Client[] Returns an array of Client objects
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

//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
