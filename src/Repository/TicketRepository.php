<?php

namespace App\Repository;

use App\Classes\Data\InternTaskStatusData;
use App\Classes\Data\UserRolesData;
use App\Entity\Client;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Ticket::class);
    $this->security = $security;
  }

  public function save(Ticket $ticket): Ticket {

    if (is_null($ticket->getId())) {
      $this->getEntityManager()->persist($ticket);
    }

    $this->getEntityManager()->flush();
    return $ticket;

  }

  public function remove(Ticket $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);
    $this->getEntityManager()->flush();
  }

  public function getTicketsPaginator(User $user, int $status): Query {

    $clients = $user->getClients();
    $company = $user->getCompany();

    if ($status == InternTaskStatusData::ZAVRSENO) {
      if ($user->getUserType() == UserRolesData::ROLE_CLIENT) {
        return $this->createQueryBuilder('c')
          ->where('c.client IN (:clients)')
          ->setParameter('clients', $clients)
          ->andWhere('c.status = :status')
          ->setParameter('status', $status)
          ->orderBy('c.status', 'ASC')
          ->addOrderBy('c.priority', 'ASC')
          ->addOrderBy('c.deadline', 'ASC')
          ->addOrderBy('c.created', 'ASC')
          ->getQuery();
      } else {
        return $this->createQueryBuilder('c')
          ->where('c.company = :company')
          ->setParameter('company', $company)
          ->andWhere('c.status = :status')
          ->setParameter('status', $status)
          ->orderBy('c.status', 'ASC')
          ->addOrderBy('c.priority', 'ASC')
          ->addOrderBy('c.deadline', 'ASC')
          ->addOrderBy('c.created', 'ASC')
          ->getQuery();
      }
    }

    if ($user->getUserType() == UserRolesData::ROLE_CLIENT) {
      return $this->createQueryBuilder('c')
        ->where('c.client IN (:clients)')
        ->setParameter('clients', $clients)
        ->andWhere('c.status <> :status')
        ->setParameter('status', InternTaskStatusData::ZAVRSENO)
//        ->andWhere('c.contact = :contact')
//        ->setParameter('contact', $user)
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->addOrderBy('c.deadline', 'ASC')
        ->addOrderBy('c.created', 'ASC')
        ->getQuery();
    } else {
      return $this->createQueryBuilder('c')
        ->where('c.company = :company')
        ->setParameter('company', $company)
        ->andWhere('c.status <> :status')
        ->setParameter('status', InternTaskStatusData::ZAVRSENO)
        ->orderBy('c.status', 'ASC')
        ->addOrderBy('c.priority', 'ASC')
        ->addOrderBy('c.deadline', 'ASC')
        ->addOrderBy('c.created', 'ASC')
        ->getQuery();
    }



  }

  public function getTicketsPaginatorByClient(Client $client): Query {

        return $this->createQueryBuilder('c')
          ->where('c.client = :client')
          ->setParameter('client', $client)
          ->orderBy('c.status', 'ASC')
          ->addOrderBy('c.priority', 'ASC')
          ->addOrderBy('c.deadline', 'ASC')
          ->addOrderBy('c.created', 'ASC')
          ->getQuery();
  }

  public function findForForm(int $id = 0): Ticket {
    if (empty($id)) {
      $ticket =  new Ticket();
      $ticket->setCompany($this->security->getUser()->getCompany());
      $ticket->setCreatedBy($this->security->getUser());
      $ticket->setContact($this->security->getUser());
      return $ticket;
    }
    $ticket = $this->getEntityManager()->getRepository(Ticket::class)->find($id);
    $ticket->setEditBy($this->security->getUser());

    return $ticket;
  }

//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ticket
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
