<?php

namespace App\Repository;

use App\Entity\Car;
use App\Entity\CarReservation;
use App\Entity\Expense;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Expense>
 *
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Expense::class);
    $this->security = $security;
  }

  public function save(Expense $expense): Expense {

    if (is_null($expense->getId())) {
      $expense->setCreatedBy($this->security->getUser());
      $this->getEntityManager()->persist($expense);
    } else {
      $expense->setEditBy($this->security->getUser());
    }

    $this->getEntityManager()->flush();

    return $expense;

  }

  public function countExpenseByCar(Car $car): int {
    $qb = $this->createQueryBuilder('e');

    $qb->select($qb->expr()->count('e'))
      ->andWhere('e.car = :car')
      ->setParameter(':car', $car);

    $query = $qb->getQuery();

    return $query->getSingleScalarResult();

  }

  public function remove(Expense $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }

  public function findForFormCar(Car $car = null): Expense {

    $expense = new Expense();
    $expense->setCar($car);
    $expense->setDate(new DateTimeImmutable());
    $expense->setCompany($car->getCompany());
    return $expense;

  }

  public function findForForm(int $id = 0): Expense {
    if (empty($id)) {
      $expense = new Expense();
      $expense->setDate(new DateTimeImmutable());
      $expense->setCompany($this->security->getUser()->getCompany());
      return $expense;
    }
    return $this->getEntityManager()->getRepository(Expense::class)->find($id);

  }

  public function getExpensesByUserPaginator(User $user) {

    return $this->createQueryBuilder('e')
      ->where('e.createdBy = :user')
      ->setParameter('user', $user)
      ->orderBy('e.isSuspended', 'ASC')
      ->addOrderBy('e.date', 'DESC')
      ->addOrderBy('e.id', 'DESC')
      ->getQuery();

  }

  public function getExpensesByCarPaginator(Car $car) {

    return $this->createQueryBuilder('e')
      ->where('e.car = :car')
      ->setParameter('car', $car)
      ->orderBy('e.isSuspended', 'ASC')
      ->addOrderBy('e.date', 'DESC')
      ->addOrderBy('e.id', 'DESC')
      ->getQuery();

  }
  public function getReport(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);



    $car = $this->getEntityManager()->getRepository(Car::class)->find($data['vozilo']);


    $startDate = $start->format('Y-m-d 00:00:00'); // Početak dana
    $endDate = $stop->format('Y-m-d 23:59:59'); // Kraj dana

    $qb = $this->createQueryBuilder('t');

    $qb
      ->where('t.date BETWEEN :startDate AND :endDate')
      ->andWhere('t.car = :car')
      ->andWhere('t.isSuspended = 0')
      ->setParameter('startDate', $startDate)
      ->setParameter('endDate', $endDate)
      ->setParameter('car', $car);

    if (isset($data['category'])) {
      $qb->andWhere($qb->expr()->in('t.type', ':categories'))
        ->setParameter('categories', $data['category']);
    }

    $rezultat = $qb->orderBy('t.date', 'DESC')
      ->getQuery();

    return $rezultat->getResult();
  }
//    /**
//     * @return Expense[] Returns an array of Expense objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Expense
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
